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
class JsonStreamReader extends JsonReader {
  protected $in;

  /**
   * Creates a new instance
   *
   * @param  io.streams.InputStream $in
   * @param  string $encoding
   */
  public function __construct(InputStream $in, $encoding= \xp::ENCODING) {
    parent::__construct($in->read(), $encoding);
    $this->in= $in;
  }

  /**
   * Pushes back a given byte sequence to be retokenized
   *
   * @param  string $bytes
   * @return void
   */
  public function pushBack($bytes) {
    $l= strlen($bytes);
    if ($l < $this->pos) {
      $this->pos-= $l;
    } else {
      $this->bytes= $bytes.substr($this->bytes, $this->pos);
      $this->len+= $l - $this->pos;
      $this->pos= 0;
    }
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
        $token= null;
        $string= '"';
        $o= 1;
        do {
          $span= strcspn($bytes, '"\\', $pos + $o) + $o;
          $end= $pos + $span;
          if ($end < $len) {
            if ('\\' === $bytes{$end}) {
              while ($end + 4 >= $len && $this->in->available()) {
                $bytes.= $this->in->read();
                $this->bytes= $bytes;
                $len= $this->len= strlen($bytes);
              }

              $string.= substr($bytes, $pos + $o, $span - $o).$this->escaped($end, $consumed);
              $o= $span + $consumed;
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
              $end++;
              break;
            }
          }
          break;
        } while ($o);
      } else if (1 === strspn($c, '{[:]},')) {
        $end= $pos + 1;
        $token= $c;
      } else if (1 === strspn($c, " \r\n\t")) {
        $pos+= strspn($bytes, " \r\n\t", $pos);
        continue;
      } else {
        $span= strcspn($bytes, "{[:]},\" \r\n\t", $pos);
        $token= substr($bytes, $pos, $span);
        $end= $pos + $span;
      }

      if ($end < $len) {
        $this->pos= $end;
        return $token;
      } else if ($this->in->available()) {
        $bytes= $this->bytes= substr($bytes, $pos).$this->in->read();
        $len= $this->len= strlen($bytes);
        $pos= 0;
        continue;
      } else {
        $this->pos= $len;
        if (null === $token) {
          throw new FormatException('Unclosed string');
        }
        return $token;
      }
    }

    return null;
  }
}