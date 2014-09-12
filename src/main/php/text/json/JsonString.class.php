<?php namespace text\json;

use lang\FormatException;

/**
 * Reads JSON from a given string
 *
 * ```php
 * $json= new JsonString('{"Hello" : "World"}');
 * $value= $json->read();
 * ```
 *
 * @test  xp://text.json.unittest.JsonStringTest
 */
class JsonString extends Input {

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
              $string.= substr($bytes, $pos + $o, $span - $o).$this->escaped($end, $consumed);
              $o= $span + $consumed;
              continue;
            } else if ('"' === $bytes{$end}) {
              $string.= substr($bytes, $pos + $o, $span + 1 - $o);
              $token= iconv($this->encoding, \xp::ENCODING, $string);
              if (false === $token) {
                $e= new FormatException('Illegal encoding');
                \xp::gc(__FILE__);
                throw $e;
              }
              $this->pos= ++$end;
              return $token;
            }
          }
          throw new FormatException('Unclosed string '.$string);
        } while ($o);
      } else if (1 === strspn($c, '{[:]},')) {
        $this->pos= $pos + 1;
        return $c;
      } else if (1 === strspn($c, " \r\n\t")) {
        $pos+= strspn($bytes, " \r\n\t", $pos);
        continue;
      } else {
        $span= strcspn($bytes, "{[:]},\" \r\n\t", $pos);
        $this->pos= $pos + $span;
        return substr($bytes, $pos, $span);
      }
    }

    return null;
  }
}