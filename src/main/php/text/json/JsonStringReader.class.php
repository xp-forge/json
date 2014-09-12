<?php namespace text\json;

use io\streams\InputStream;
use lang\FormatException;

/**
 * Reads JSON from a given input stream
 *
 * ```php
 * $json= new JsonStreamReader((new File('input.json'))->getInputStream()));
 * $value= $json->read();
 * ```
 */
class JsonStringReader extends JsonReader {
  protected $bytes;
  protected $len;
  protected $pos;
  protected $encoding;

  /**
   * Creates a new instance
   *
   * @param  string $in
   * @param  string $encoding
   */
  public function __construct($in, $encoding= \xp::ENCODING) {
    $this->bytes= $in;
    $this->len= strlen($this->bytes);
    $this->pos= 0;
    $this->encoding= $encoding;
  }

  /**
   * Pushes back a given byte sequence to be retokenized
   *
   * @param  string $bytes
   * @return void
   */
  public function pushBack($bytes) {
    $this->pos-= strlen($bytes);
  }

  /**
   * Returns next token
   *
   * @return string
   */
  public function nextToken() {
    $pos= $this->pos;
    $len= $this->len;
    $bytes= $this->bytes;
    while ($pos < $len) {
      $c= $this->bytes{$pos};
      if ('"' === $c) {
        $string= '"';
        $o= 1;
        do {
          $span= strcspn($bytes, '"\\', $pos + $o) + $o;
          $end= $pos + $span;
          if ($end < $len) {
            if ('\\' === $bytes{$end}) {
              $string.= substr($bytes, $pos + $o, $span - $o);
              $escape= $bytes{$end + 1};
              if (isset(self::$escapes[$escape])) {
                $string.= self::$escapes[$escape];
                $o= $span + 2;
              } else if ('u' === $escape) {
                $hex= substr($bytes, $end + 2, 4);
                $string.= iconv('ucs-4be', $this->encoding, pack('N', hexdec($hex)));
                $o= $span + 6;
              } else {
                throw new FormatException('Illegal escape sequence \\'.$escape.'...');
              }
              continue;
            } else if ('"' === $bytes{$end}) {
              $string.= substr($bytes, $pos + $o, $span + 1 - $o);
              // echo "STRING<$this->encoding> = '", addcslashes($string, "\0..\17"), "'\n";
              $token= iconv($this->encoding, \xp::ENCODING, $string);
              if (\xp::errorAt(__FILE__, __LINE__ - 1)) {
                $e= new FormatException('Illegal encoding');
                \xp::gc(__FILE__);
                throw $e;
              }
              $this->pos= ++$end;
              return $token;
            }
          }
          throw new FormatException('Unclosed string');
        } while ($o);
      } else if (1 === strspn($c, '{[:]},')) {
        $this->pos= $pos + 1;
        return $c;
      } else if (1 === strspn($c, " \r\n\t")) {
        $pos+= strspn($bytes, " \r\n\t", $pos);
        continue;
      } else {
        $span= strcspn($bytes, "{[:]},\" \r\n\t", $pos);
        $token= substr($bytes, $pos, $span);
        $this->pos= $pos + $span;
        return $token;
      }
    }

    return null;
  }
}