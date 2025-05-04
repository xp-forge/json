<?php namespace text\json;

use lang\FormatException;

/**
 * Reads JSON from a given string
 *
 * ```php
 * $json= new StringInput('{"Hello" : "World"}');
 * $value= $json->read();
 * ```
 *
 * @test  text.json.unittest.StringInputTest
 */
class StringInput extends Input {

  /**
   * Resets input
   *
   * @return void
   * @throws io.IOException If this input cannot be reset
   */
  public function reset() {
    $this->pos= 0;
    $this->firstToken= null;
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
      $c= $bytes[$pos];
      if ('"' === $c) {
        $string= '';
        $o= $pos + 1;
        do {
          $end= strcspn($bytes, '"\\', $o) + $o;
          if ($end < $len) {
            if ('\\' === $bytes[$end]) {
              $string.= substr($bytes, $o, $end - $o).$this->escaped($end, $len, $consumed);
              $o= $end + $consumed;
              continue;
            } else if ($c === $bytes[$end]) {
              $string.= substr($bytes, $o, $end - $o);
              $encoded= iconv($this->encoding, \xp::ENCODING, $string);
              if (false === $encoded) {
                $e= new FormatException('Illegal '.$this->encoding.' encoding');
                \xp::gc(__FILE__);
                throw $e;
              }
              $this->pos= ++$end;
              return [true, $encoded];
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