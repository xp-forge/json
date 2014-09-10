<?php namespace text\json;

use io\streams\InputStream;
use text\StreamTokenizer;
use lang\FormatException;

/**
 * Reads JSON from a given input stream
 *
 * ```php
 * $json= new JsonReader();
 * $value= $json->read((new File('input.json'))->getInputStream());
 * ```
 */
class JsonReader extends \lang\Object {
  const WHITESPACE = " \n\r\t";
  protected $encoding;

  /**
   * Creates a new reader
   *
   * @param  string $encoding
   */
  public function __construct($encoding= \xp::ENCODING) {
    $this->encoding= $encoding;
  }

  /**
   * Reads a map
   *
   * @param  text.Tokenizer $t
   * @return [:var]
   * @throws lang.FormatException
   */
  protected function readObject($t) {
    $map= [];
    $key= null;
    $next= true;
    while (null !== ($token= $t->nextToken())) {
      if ('}' === $token && null === $key) {
        return $map;
      } else if (':' === $token && is_string($key)) {
        $map[$key]= $this->readValue($t);
        $key= null;
      } else if (',' === $token) {
        $key= $this->readValue($t);
      } else if (strpos(self::WHITESPACE, $token) !== false) {
        continue;
      } else if ($next) {
        $t->pushBack($token);
        $key= $this->readValue($t);
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
   * @param  text.Tokenizer $t
   * @return [:var]
   * @throws lang.FormatException
   */
  protected function readList($t) {
    $list= [];
    $next= true;
    while (null !== ($token= $t->nextToken())) {
      if (']' === $token) {
        return $list;
      } else if (',' === $token) {
        $list[]= $this->readValue($t);
      } else if (strpos(self::WHITESPACE, $token) !== false) {
        continue;
      } else if ($next) {
        $t->pushBack($token);
        $list[]= $this->readValue($t);
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
   * @param  text.Tokenizer $t
   * @return string
   * @throws lang.FormatException
   */
  protected function readString($t) {
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
    while (null !== ($token= $t->nextToken('"\\'))) {
      if ('"' === $token) {
        $encoded= iconv($this->encoding, \xp::ENCODING, $string);
        if (\xp::errorAt(__FILE__, __LINE__ - 1)) {
          $e= new FormatException('Illegal encoding');
          \xp::gc(__FILE__);
          throw $e;
        }
        return $encoded;
      } else if ('\\' === $token) {
        $escape= $t->nextToken('"\\bfnrtu');
        if ('u' === $escape) {
          for ($hex= '', $i= 0; $i < 4; $i++) {
            $hex.= $t->nextToken('0123456789abcdefABCDEF');
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
   * @param  text.Tokenizer $t
   * @return var
   * @throws lang.FormatException
   */
  protected function readValue($t) {
    static $keyword= [
      'true'   => [true],
      'false'  => [false],
      'null'   => [null],
    ];

    while (null !== ($token= $t->nextToken())) {
      if ('{' === $token) {
        return $this->readObject($t);
      } else if ('[' === $token) {
        return $this->readList($t);
      } else if ('"' === $token) {
        return $this->readString($t);
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
   * @param  io.streams.InputStream $in
   * @return var
   */
  public function read(InputStream $in) {
    $t= new StreamTokenizer($in, '{[,"]}:'.self::WHITESPACE, true);
    $value= $this->readValue($t);

    while ($t->hasMoreTokens()) {
      $token= $t->nextToken();
      if (strpos(self::WHITESPACE, $token) === false) {
        throw new FormatException('Junk after end of value ['.\xp::stringOf($token).']');
      }
    }

    return $value;
  }
}