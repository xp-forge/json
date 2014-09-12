<?php namespace text\json;

use io\streams\InputStream;
use lang\IllegalStateException;

class JsonTokenizer extends \lang\Object {
  protected $in;
  protected $tokens= null;

  /**
   * Creates a new instance
   *
   * @param  io.streams.InputStream $in
   */
  public function __construct(InputStream $in) {
    $this->in= $in;
    $this->bytes= $this->in->read();
    $this->len= strlen($this->bytes);
    $this->pos= 0;
    $this->eof= false;
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
    $pos= $this->pos;
    $len= $this->len;
    $bytes= $this->bytes;
    while ($pos < $this->len) {
      $c= $this->bytes{$pos};
      if ('"' === $c) {
        $o= 1;
        do {
          $span= strcspn($bytes, '"\\', $pos + $o) + $o;
          if ($pos + $span < $len && '\\' === $bytes{$pos + $span}) {
            $o= $span + 1 + 1;
            continue;
          }
          break;
        } while ($o);
        $span++;
      } else if (false !== strpos('{[:]},', $c)) {
        $span= 1;
      } else if (false !== strpos(" \r\n\t", $c)) {
        $pos+= strspn($bytes, " \r\n\t", $pos + 1) + 1;
        continue;
      } else {
        $span= strcspn($bytes, "{[:]},\" \r\n\t", $pos);
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
      $token= substr($bytes, $pos, $span);
      $this->pos= $pos + $span;
      return $token;
    }

    return null;
  }
}
