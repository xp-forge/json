<?php namespace text\json;

use lang\FormatException;

/**
 * Base class for JSON input implementations
 *
 * @see  xp://text.json.JsonString
 * @see  xp://text.json.JsonStream
 */
abstract class Input extends \lang\Object {
  protected $bytes;
  protected $len;
  protected $pos;
  protected $encoding;
  protected $firstToken= null;

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
   */
  public function __construct($source, $encoding= \xp::ENCODING) {
    $this->bytes= $source;
    $this->len= strlen($this->bytes);
    $this->pos= 0;
    $this->encoding= $encoding;
  }

  /**
   * Processes an escape sequence
   *
   * @param  int $pos The position
   * @param  int &$offset How many bytes were consumed
   * @return string
   * @throws lang.FormatException
   */
  protected function escaped($pos, &$offset) {
    $escape= $this->bytes{$pos + 1};
    if (isset(self::$escapes[$escape])) {
      $offset= 2;
      return self::$escapes[$escape];
    } else if ('u' === $escape) {
      $offset= 6;
      $hex= substr($this->bytes, $pos + 2, 4);
      return iconv('ucs-4be', $this->encoding, pack('N', hexdec($hex)));
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
  protected function readObject() {
    $token= $this->nextToken();
    if ('}' === $token) {
      return [];
    } else if (null !== $token) {
      $result= [];
      do {
        $key= $this->nextValue($token);
        if (!is_string($key)) {
          throw new FormatException('Illegal key type '.typeof($key).', expecting string');
        }
        if (':' === ($token= $this->nextToken())) {
          $result[$key]= $this->nextValue();
        } else {
          throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading object, expecting ":"');
        }

        $delim= $this->nextToken();
        if (',' === $delim) {
          continue;
        } else if ('}' === $delim) {
          return $result;
        }
      } while ($token= $this->nextToken());
    }

    throw new FormatException('Unclosed object');
  }

  /**
   * Reads a list
   *
   * @return [:var]
   * @throws lang.FormatException
   */
  protected function readList() {
    $token= $this->nextToken();
    if (']' === $token) {
      return [];
    } else if (null !== $token) {
      $result= [];
      do {
        $result[]= $this->nextValue($token);
        $delim= $this->nextToken();
        if (',' === $delim) {
          continue;
        } else if (']' === $delim) {
          return $result;
        }
      } while ($token= $this->nextToken());
    }

    throw new FormatException('Unclosed list');
  }

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
   * @return var
   * @throws lang.FormatException
   */
  public function nextValue($token= null) {
    $token= null === $token ? $this->nextToken() : $token;
    if ('"' === $token{0}) {
      return substr($token, 1, -1);
    } else if ('{' === $token) {
      return $this->readObject();
    } else if ('[' === $token) {
      return $this->readList();
    } else if ('true' === $token) {
      return true;
    } else if ('false' === $token) {
      return false;
    } else if ('null' === $token) {
      return null;
    } else if (is_numeric($token)) {
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
    if ('"' === $token{0}) {
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
    $value= $this->nextValue($this->firstToken());

    if (null !== ($token= $this->nextToken())) {
      throw new FormatException('Junk after end of value ['.\xp::stringOf($token).']');
    }
    return $value;
  }

  /**
   * Reads elements from an input stream sequentially
   *
   * @return php.Iterator
   */
  public function elements() {
    return new Elements($this);
  }

  /**
   * Reads key/value pairs from an input stream sequentially
   *
   * @return php.Iterator
   */
  public function pairs() {
    return new Pairs($this);
  }
}