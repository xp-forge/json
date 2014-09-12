<?php namespace text\json;

use text\Tokenizer;
use lang\FormatException;

/**
 * Base class for JSON readers
 */
abstract class JsonReader extends \lang\Object {
  const WHITESPACE = " \n\r\t";

  /**
   * Creates a new stream reader to read from a stream
   *
   * @param  text.Tokenizer $tokenizer
   * @param  string $encoding
   */
  public function __construct(Tokenizer $tokenizer, $encoding= \xp::ENCODING) {
    $this->tokenizer= $tokenizer;
    $this->encoding= $encoding;
  }

  /**
   * Resets tokenizer
   *
   * @return void
   */
  public function reset() {
    $this->tokenizer->reset();
  }

  /**
   * Fetches next token
   *
   * @return string
   */
  public function nextToken() {
    while (null !== ($token= $this->tokenizer->nextToken())) {
      if (strpos(self::WHITESPACE, $token) !== false) continue;
      break;
    }
    return $token;
  }

  /**
   * Reads a map
   *
   * @return [:var]
   * @throws lang.FormatException
   */
  public function nextObject() {
    $map= [];
    $key= null;
    $next= true;
    while (null !== ($token= $this->tokenizer->nextToken())) {
      if ('}' === $token && null === $key) {
        return $map;
      } else if (':' === $token && is_string($key)) {
        $map[$key]= $this->nextValue();
        $key= null;
      } else if (',' === $token) {
        $key= $this->nextValue();
      } else if (strpos(self::WHITESPACE, $token) !== false) {
        continue;
      } else if ($next) {
        $this->tokenizer->pushBack($token);
        $key= $this->nextValue();
        $next= false;
      } else {
        throw new FormatException('Unexpected key - missing comma?');
      }
    }
    throw new FormatException('Unclosed object');
  }

  /**
   * Reads a list
   *
   * @return [:var]
   * @throws lang.FormatException
   */
  public function nextList() {
    $list= [];
    $next= true;
    while (null !== ($token= $this->tokenizer->nextToken())) {
      if (']' === $token) {
        return $list;
      } else if (',' === $token) {
        $list[]= $this->nextValue();
      } else if (strpos(self::WHITESPACE, $token) !== false) {
        continue;
      } else if ($next) {
        $this->tokenizer->pushBack($token);
        $list[]= $this->nextValue();
        $next= false;
      } else {
        throw new FormatException('Unexpected value - missing comma?');
      }
    }
    throw new FormatException('Unclosed list');
  }

  /**
   * Reads a string
   *
   * @return string
   * @throws lang.FormatException
   */
  public function nextString() {
    static $escapes= [
      '"'  => "\"",
      'b'  => "\b",
      'f'  => "\f",
      'n'  => "\n",
      'r'  => "\r",
      't'  => "\t",
      '\\' => "\\",
      '/'  => '/'
    ];

    $string= '';
    while (null !== ($token= $this->tokenizer->nextToken('"\\'))) {
      if ('"' === $token) {
        $encoded= iconv($this->encoding, \xp::ENCODING, $string);
        if (\xp::errorAt(__FILE__, __LINE__ - 1)) {
          $e= new FormatException('Illegal encoding');
          \xp::gc(__FILE__);
          throw $e;
        }
        return $encoded;
      } else if ('\\' === $token) {
        $escape= $this->tokenizer->nextToken('"\\bfnrtu');
        if ('u' === $escape) {
          for ($hex= '', $i= 0; $i < 4; $i++) {
            $hex.= $this->tokenizer->nextToken('0123456789abcdefABCDEF');
          }
          $string.= iconv('ucs-4be', $this->encoding, pack('N', hexdec($hex)));
        } else if (isset($escapes[$escape])) {
          $string.= $escapes[$escape];
        } else {
          throw new FormatException('Illegal escape sequence \\'.$escape.'...');
        }
      } else {
        $string.= $token;
      }
    }
    throw new FormatException('Unclosed string');
  }

  /**
   * Reads a value
   *
   * @return var
   * @throws lang.FormatException
   */
  public function nextValue() {
    static $keyword= [
      'true'   => [true],
      'false'  => [false],
      'null'   => [null],
    ];

    while (null !== ($token= $this->tokenizer->nextToken())) {
      if ('{' === $token) {
        return $this->nextObject();
      } else if ('[' === $token) {
        return $this->nextList();
      } else if ('"' === $token) {
        return $this->nextString();
      } else if (isset($keyword[$token])) {
        return $keyword[$token][0];
      } else if (strpos(self::WHITESPACE, $token) !== false) {
        continue;
      } else if (is_numeric($token)) {
        return $token > PHP_INT_MAX || $token < -PHP_INT_MAX- 1 || strcspn($token, '.eE') < strlen($token)
          ? (double)$token
          : (int)$token
        ;
      } else {
        throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading value');
      }
    }

    throw new FormatException('Empty input');
  }

  /**
   * Reads a value from an input stream
   *
   * @return var
   */
  public function read() {
    $value= $this->nextValue();

    while ($this->tokenizer->hasMoreTokens()) {
      $token= $this->tokenizer->nextToken();
      if (strpos(self::WHITESPACE, $token) === false) {
        throw new FormatException('Junk after end of value ['.\xp::stringOf($token).']');
      }
    }

    return $value;
  }

  /**
   * Reads elements from an input stream sequentially
   *
   * @return var
   */
  public function elements() {
    return new Elements($this);
  }

  /**
   * Reads key/value pairs from an input stream sequentially
   *
   * @return var
   */
  public function pairs() {
    return new Pairs($this);
  }
}