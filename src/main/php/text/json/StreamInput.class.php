<?php namespace text\json;

use io\streams\InputStream;
use io\streams\Seekable;
use io\IOException;
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
  protected $in, $offset;

  /**
   * Creates a new instance
   *
   * @see    http://www.ietf.org/rfc/rfc4627.txt, section 3: Encoding detection
   * @param  io.streams.InputStream $in
   * @param  string $encoding
   * @param  int $maximumNesting Maximum nesting level, defaults to 512
   */
  public function __construct(InputStream $in, $encoding= \xp::ENCODING, $maximumNesting= 512) {
    $bytes= $in->read(4);
    $length= strlen($bytes);

    if ($length > 1 && "\xfe" === $bytes{0} && "\xff" === $bytes{1}) {

      // UTF-16 BOM
      $this->in= new MultiByteSource($in, 'utf-16be');
      $this->offset= 2;
      $initial= iconv('utf-16be', 'utf-8', substr($bytes, 2));
      $encoding= \xp::ENCODING;
    } else if ($length > 1 && "\xff" === $bytes{0} && "\xfe" === $bytes{1}) {

      // UTF-16 BOM
      $this->in= new MultiByteSource($in, 'utf-16le');
      $this->offset= 2;
      $initial= iconv('utf-16le', 'utf-8', substr($bytes, 2));
      $encoding= \xp::ENCODING;
    } else if ($length > 2 && "\xef" === $bytes{0} && "\xbb"  === $bytes{1} && "\xbf"  === $bytes{2}) {

      // UTF-8 BOM
      $this->in= $in;
      $this->offset= 3;
      $initial= $length > 3 ? $bytes{3} : '';
      $encoding= 'utf-8';
    } else if ($length > 3 && "\x00" === $bytes{0} && "\x00" !== $bytes{1} && "\x00" === $bytes{2} && "\x00" !== $bytes{3}) {

      // Encoding detection: 00 xx 00 xx -> UTF-16BE
      $this->in= new MultiByteSource($in, 'utf-16be');
      $this->offset= 0;
      $initial= iconv('utf-16be', 'utf-8', $bytes);
      $encoding= \xp::ENCODING;
    } else if ($length > 3 && "\x00" !== $bytes{0} && "\x00" === $bytes{1} && "\x00" !== $bytes{2} && "\x00" === $bytes{3}) {

      // Encoding detection: xx 00 xx 00 -> UTF-16LE
      $this->in= new MultiByteSource($in, 'utf-16le');
      $this->offset= 0;
      $initial= iconv('utf-16le', 'utf-8', $bytes);
      $encoding= \xp::ENCODING;
    } else {

      // Use encoding given via parameter
      $this->in= $in;
      $this->offset= 0;
      $initial= $bytes;
    }

    parent::__construct($initial.$this->in->read(), $encoding, $maximumNesting);
  }

  /**
   * Resets input
   *
   * @return void
   * @throws io.IOException If this input cannot be reset
   */
  public function reset() {
    if ($this->in instanceof Seekable) {
      $this->in->seek($this->offset);
      $this->bytes= $this->in->read();
      $this->len= strlen($this->bytes);
      $this->pos= 0;
      $this->firstToken= null;
    } else {
      throw new IOException('Cannot seek streams of type '.typeof($this->in)->getName());
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

              $string.= substr($bytes, $o, $end - $o).$this->escaped($end, $len, $consumed);
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