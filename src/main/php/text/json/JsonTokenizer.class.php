<?php namespace text\json;

use io\streams\InputStream;
use lang\FormatException;

class JsonTokenizer extends \lang\Object {
  protected $in;
  protected $bytes;
  protected $len;
  protected $pos;
  protected $encoding;

  /**
   * Creates a new instance
   *
   * @param  io.streams.InputStream $in
   * @param  string $encoding
   */
  public function __construct(InputStream $in, $encoding= \xp::ENCODING) {
    $this->in= $in;
    $this->bytes= $this->in->read();
    $this->len= strlen($this->bytes);
    $this->pos= 0;
    $this->encoding= $encoding;
  }

  /**
   * Resets tokenizer
   *
   * @return void
   */
  public function reset() {
    // NOOP
  }

  /**
   * Pushes back a given byte sequence to be retokenized
   *
   * @param  string $bytes
   * @return void
   */
  public function backup($bytes) {
    $this->bytes= $bytes.substr($this->bytes, $this->pos);
    $this->pos= 0;
    $this->len= strlen($this->bytes);
  }

  /**
   * Returns next token
   *
   * @return string
   */
  public function next() {
    static $escapes= [
      '"'  => "\"",
      'b'  => "\x08",
      'f'  => "\x0c",
      'n'  => "\x0a",
      'r'  => "\x0d",
      't'  => "\x09",
      '\\' => "\\",
      '/'  => '/'
    ];

    $pos= $this->pos;
    $len= $this->len;
    $bytes= $this->bytes;
    while ($pos < $this->len) {
      $c= $this->bytes{$pos};
      if ('"' === $c) {
        $token= null;
        $string= '"';
        $o= 1;
        do {
          $span= strcspn($bytes, '"\\', $pos + $o) + $o;
          if ($pos + $span < $len) {
            if ('\\' === $bytes{$pos + $span}) {
              $string.= substr($bytes, $pos + $o, $span - $o);
              $escape= $bytes{$pos + $span + 1};
              if (isset($escapes[$escape])) {
                $string.= $escapes[$escape];
              } else if ('u' === $escape) {
                $hex= substr($bytes, $pos + $span + 1 + 1, 4);
                $string.= iconv('ucs-4be', $this->encoding, pack('N', hexdec($hex)));
                $span+= 4;
              } else {
                throw new FormatException('Illegal escape sequence \\'.$escape.'...');
              }

              $o= $span + 1 + 1;
              continue;
            } else if ('"' === $bytes{$pos + $span}) {
              $string.= substr($bytes, $pos + $o, ++$span - $o);
              // echo "STRING<$this->encoding> = '", addcslashes($string, "\0..\17"), "'\n";
              $token= iconv($this->encoding, \xp::ENCODING, $string);
              if (\xp::errorAt(__FILE__, __LINE__ - 1)) {
                $e= new FormatException('Illegal encoding');
                \xp::gc(__FILE__);
                throw $e;
              }
              break;
            }
          }
          break;
        } while ($o);
      } else if (false !== strpos('{[:]},', $c)) {
        $span= 1;
        $token= $c;
      } else if (false !== strpos(" \r\n\t", $c)) {
        $pos+= strspn($bytes, " \r\n\t", $pos + 1) + 1;
        continue;
      } else {
        $span= strcspn($bytes, "{[:]},\" \r\n\t", $pos);
        $token= substr($bytes, $pos, $span);
      }

      if ($pos + $span >= $len) {
        if ($this->in->available()) {
          $bytes= $this->bytes= substr($bytes, $pos).$this->in->read();
          $len= $this->len= strlen($bytes);
          $pos= 0;
          continue;
        }
      }

      //echo "bytes[$pos..", $span + $pos ,"/$this->len]= '", addcslashes(substr($this->bytes, $pos, $span), "\0..\17"), "'\n";
      $this->pos= $pos + $span;
      return $token;
    }

    return null;
  }
}
