<?php namespace text\json;

use lang\FormatException;

/**
 * Base class for JSON input implementations
 *
 * @see  xp://text.json.JsonString
 * @see  xp://text.json.JsonStream
 */
abstract class Input {
  protected $bytes;
  protected $len;
  protected $pos;
  protected $encoding;
  protected $firstToken= null;
  protected $maximumNesting= 512;

  protected static $escapes= [
    '"'  => "\"",
    'b'  => "\x08",
    'f'  => "\x0c",
    'n'  => "\x0a",
    'r'  => "\x0d",
    't'  => "\x09",
    '\\' => "\\",
    '/'  => '/'
  ];

  /**
   * Creates a new instance
   *
   * @param  string $source
   * @param  string $encoding
   * @param  int $maximumNesting Maximum nesting level, defaults to 512
   */
  public function __construct($source, $encoding= \xp::ENCODING, $maximumNesting= 512) {
    $this->bytes= $source;
    $this->len= strlen($this->bytes);
    $this->pos= 0;
    $this->encoding= $encoding;
    $this->maximumNesting= $maximumNesting;
  }

  /**
   * Processes an escape sequence
   *
   * @param  int $pos The position
   * @param  int $len The string length
   * @param  int &$offset How many bytes were consumed
   * @return string
   * @throws lang.FormatException
   */
  protected function escaped($pos, $len, &$offset) {
    if ($len - $pos < 2) {
      throw new FormatException('Unclosed escape sequence');
    }

    $escape= $this->bytes{$pos + 1};
    if (isset(self::$escapes[$escape])) {
      $offset= 2;
      return self::$escapes[$escape];
    } else if ('u' === $escape) {
      if (1 !== sscanf(substr($this->bytes, $pos + 2, 4), '%4x', $hex)) {
        throw new FormatException('Illegal unicode escape sequence '.substr($this->bytes, $pos, 6));
      } else if ($hex > 0xd800 && $hex < 0xdfff) {
        $offset= 12;
        $surrogate= hexdec(substr($this->bytes, $pos + 8, 4));
        $char= ($hex << 10) + $surrogate + 0xfca02400;  // surrogate offset: 0x10000 - (0xd800 << 10) - 0xdc00
        return iconv('ucs-4be', $this->encoding, pack('N', $char));
      } else {
        $offset= 6;
        return iconv('ucs-4be', $this->encoding, pack('N', $hex));
      }
    } else {
      throw new FormatException('Illegal escape sequence \\'.$escape.'...');
    }
  }

  /**
   * Reads an object
   *
   * @return [:var]
   * @throws lang.FormatException
   */
  protected function readObject($nesting) {
    $token= $this->nextToken();
    if ('}' === $token) {
      return [];
    } else if (null !== $token) {
      $result= [];
      if ($nesting++ > $this->maximumNesting) {
        throw new FormatException('Nesting level too deep');
      }
      do {
        $key= $this->valueOf($token, $nesting);
        if (!is_string($key)) {
          throw new FormatException('Illegal key type '.typeof($key).', expecting string');
        }
        if (':' === ($token= $this->nextToken())) {
          $result[$key]= $this->valueOf($this->nextToken(), $nesting);
        } else {
          throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading object, expecting ":"');
        }

        $delim= $this->nextToken();
        if (',' === $delim) {
          continue;
        } else if ('}' === $delim) {
          return $result;
        } else {
          throw new FormatException('Unexpected '.\xp::stringOf($delim).', expecting "," or "}"');
        }
      } while (null !== ($token= $this->nextToken()));
    }

    throw new FormatException('Unclosed object');
  }

  /**
   * Reads an array
   *
   * @return [:var]
   * @throws lang.FormatException
   */
  protected function readArray($nesting) {
    $token= $this->nextToken();
    if (']' === $token) {
      return [];
    } else if (null !== $token) {
      $result= [];
      if ($nesting++ > $this->maximumNesting) {
        throw new FormatException('Nesting level too deep');
      }
      do {
        $result[]= $this->valueOf($token, $nesting);
        $delim= $this->nextToken();
        if (',' === $delim) {
          continue;
        } else if (']' === $delim) {
          return $result;
        } else {
          throw new FormatException('Unexpected '.\xp::stringOf($delim).', expecting "," or "]"');
        }
      } while (null !== ($token= $this->nextToken()));
    }

    throw new FormatException('Unclosed list');
  }

  /**
   * Resets input
   *
   * @return void
   * @throws io.IOException If this input cannot be reset
   */
  public abstract function reset();

  /**
   * Returns first token
   *
   * @return string
   */
  public function firstToken() {
    if (null === $this->firstToken) {
      $this->firstToken= $this->nextToken();
    }
    return $this->firstToken;
  }

  /**
   * Returns next token
   *
   * @return string
   */
  public abstract function nextToken();

  /**
   * Reads a value
   *
   * @param  string $token
   * @return var
   * @throws lang.FormatException
   */
  public function valueOf($token, $nesting= 0) {
    if (true === $token[0]) {
      return $token[1];
    } else if ('{' === $token) {
      return $this->readObject($nesting);
    } else if ('[' === $token) {
      return $this->readArray($nesting);
    } else if ('true' === $token) {
      return true;
    } else if ('false' === $token) {
      return false;
    } else if ('null' === $token) {
      return null;
    } else if (preg_match('/^\-?(0|[1-9][0-9]*)(\.[0-9]+)?([eE][+-]?[0-9]+)?$/', $token)) {
      return $token > PHP_INT_MAX || $token < -PHP_INT_MAX- 1 || strcspn($token, '.eE') < strlen($token)
        ? (double)$token
        : (int)$token
      ;
    } else if (null === $token) {
      throw new FormatException('Empty input');
    } else {
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading value');
    }
  }

  /**
   * Returns the data type
   *
   * @return string
   */
  public function type() {
    $token= $this->firstToken();
    if (true === $token[0]) {
      return Types::$STRING;
    } else if ('{' === $token) {
      return Types::$OBJECT;
    } else if ('[' === $token) {
      return Types::$ARRAY;
    } else if ('true' === $token) {
      return Types::$TRUE;
    } else if ('false' === $token) {
      return Types::$FALSE;
    } else if ('null' === $token) {
      return Types::$NULL;
    } else if (is_numeric($token)) {
      return $token > PHP_INT_MAX || $token < -PHP_INT_MAX- 1 || strcspn($token, '.eE') < strlen($token)
        ? Types::$DOUBLE
        : Types::$INT
      ;
    } else {
      return null;
    }
  }

  /**
   * Reads a value
   *
   * @return var
   */
  public function read() {
    $value= $this->valueOf($this->firstToken());

    $test= $this->nextToken();
    $this->close();
    if (null !== $test) {
      throw new FormatException('Junk after end of value ['.\xp::stringOf($test).']');
    }

    return $value;
  }

  /**
   * Reads elements from an input stream sequentially
   *
   * @return text.json.Elements
   */
  public function elements() {
    return new Elements($this);
  }

  /**
   * Reads key/value pairs from an input stream sequentially
   *
   * @return text.json.Pairs
   */
  public function pairs() {
    return new Pairs($this);
  }

  /** @return void */
  public function close() {
    // Does nothing
  }

  /** @return void */
  public function __destruct() {
    $this->close();
  }
}