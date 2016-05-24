<?php namespace text\json;

use io\streams\InputStream;
use lang\FormatException;

/**
 * Reads JSON from a given input stream
 *
 * ```php
 * $json= new StreamInput((new File('input.json'))->getInputStream()));
 * $value= $json->read();
 * ```
 *
 * @test  xp://text.json.unittest.StreamInputTest
 */
class StreamInput extends Input {
  protected $in;

  /**
   * Creates a new instance
   *
   * @param  io.streams.InputStream $in
   * @param  string $encoding
   */
  public function __construct(InputStream $in, $encoding= \xp::ENCODING) {
    $initial= '';
    $bom= $in->read(2);
    if ("\376\377" === $bom) {
      $encoding= \xp::ENCODING;
      $this->in= new MultiByteSource($in, 'utf-16be');
    } else if ("\377\376" === $bom) {
      $encoding= \xp::ENCODING;
      $this->in= new MultiByteSource($in, 'utf-16le');
    } else {
      $bom.= $in->read(1);
      if ("\357\273\277" === $bom) {
        $encoding= 'utf-8';
      } else {
        $initial= $bom;
      }
      $this->in= $in;
    }

    parent::__construct($initial.$this->in->read(), $encoding);
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
      $c= $bytes{$pos};
      if ('"' === $c) {
        $token= null;
        $string= '';
        $o= $pos + 1;
        do {
          $end= strcspn($bytes, '"\\', $o) + $o;
          if ($end < $len) {
            if ('\\' === $bytes{$end}) {

              // Ensure either EOF or space for a surrogate pair
              while ($end + 12 > $len && $this->in->available()) {
                $bytes.= $this->in->read();
                $this->bytes= $bytes;
                $len= $this->len= strlen($bytes);
              }

              $string.= substr($bytes, $o, $end - $o).$this->escaped($end, $consumed);
              $o= $end + $consumed;
              continue;
            } else if ($c === $bytes{$end}) {
              $string.= substr($bytes, $o, $end - $o);
              $encoded= iconv($this->encoding, \xp::ENCODING, $string);
              if (false === $encoded) {
                $e= new FormatException('Illegal '.$this->encoding.' encoding');
                \xp::gc(__FILE__);
                throw $e;
              }
              $end++;
              $token= [true, $encoded];
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
        if ($pos < $len) continue;
        $end= $len;
        $token= $string= null;
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
        if (null === $token && null !== $string) {
          throw new FormatException('Unclosed string '.$string);
        }
        return $token;
      }
    }

    return null;
  }

  /** @return void */
  public function close() { $this->in->close(); }
}