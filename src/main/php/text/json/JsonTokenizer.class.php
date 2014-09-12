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
   * Returns whether more tokens are available
   *
   * @return bool
   */
  public function hasNext() {
    return $this->pos < $this->len;
  }

  /**
   * Returns next token
   *
   * @return string
   */
  public function next() {
    while ($this->pos < $this->len) {
      if ('"' === $this->bytes{$this->pos}) {
        $o= 1;
        do {
          $span= strcspn($this->bytes, '"\\', $this->pos + $o) + $o;
          if ($this->pos + $span < $this->len && '\\' === $this->bytes{$this->pos + $span}) {
            $o= $span + 1 + 1;
            continue;
          }
          break;
        } while ($o);
        //echo "STR: ";
        $span++;
      } else if (false !== strpos('{[:]},', $this->bytes{$this->pos})) {
        $span= 1;
        //echo "TOK: ";
      } else if (false !== strpos(" \r\n\t", $this->bytes{$this->pos})) {
        $span= strspn($this->bytes, " \r\n\t", $this->pos + 1) + 1; 
        $this->pos+= $span;
        continue;
        //echo "W/S: ";
      } else {
        $span= strcspn($this->bytes, "{[:]},\" \r\n\t", $this->pos);
        //echo "WRD: ";
      }

      if ($this->pos + $span >= $this->len) {
        //echo "underrun\n";
        if ($this->in->available()) {
          $this->bytes= substr($this->bytes, $this->pos).$this->in->read();
          $this->pos= 0;
          $this->len= strlen($this->bytes);
          continue;
        }
      }

      //echo "bytes[$this->pos..", $span + $this->pos ,"/$this->len]= '", addcslashes(substr($this->bytes, $this->pos, $span), "\0..\17"), "'\n";
      $token= substr($this->bytes, $this->pos, $span);
      $this->pos+= $span;
      return $token;
    }

    return null;
  }
}
