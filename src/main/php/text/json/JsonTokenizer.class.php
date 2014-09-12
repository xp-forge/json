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
    $this->reset();
  }

  /**
   * Resets tokenizer
   *
   * @return void
   */
  public function reset() {
    if (null == $this->tokens) {
      $f= function() {
        $bytes= $this->in->read();
        $tokens= [];
        for ($i= 0, $l= strlen($bytes); $i < $l; ) {
          if ('"' === $bytes{$i}) {
            $o= 1;
            do {
              $span= strcspn($bytes, '"\\', $i + $o) + $o;
              if ($i + $span < $l && '\\' === $bytes{$i + $span}) {
                $o= $span + 1 + 1;
                continue;
              }
              break;
            } while ($o);
            //echo "STR: ";
            $span++;
          } else if (false !== strpos('{[:]},', $bytes{$i})) {
            $span= 1;
            //echo "TOK: ";
          } else if (false !== strpos(" \r\n\t", $bytes{$i})) {
            $span= strspn($bytes, " \r\n\t", $i + 1) + 1; 
            $i+= $span;
            continue;
            //echo "W/S: ";
          } else {
            $span= strcspn($bytes, "{[:]},\" \r\n\t", $i);
            //echo "WRD: ";
          }

          if ($i + $span >= $l) {
            //echo "underrun\n";
            if ($this->in->available()) {
              $bytes= substr($bytes, $i).$this->in->read();
              $i= 0;
              $l= strlen($bytes);
              continue;
            }
          }

          //echo "bytes[$i..", $span + $i ,"/$l]= '", addcslashes(substr($bytes, $i, $span), "\0..\17"), "'\n";
          yield substr($bytes, $i, $span);
          $i+= $span;
        }
      };
      $this->tokens= $f();
    }
  }

  /**
   * Pushes back a given byte sequence to be retokenized
   *
   * @param  string $bytes
   * @return void
   */
  public function backup($bytes) {
    $this->current= $bytes;
  }

  /**
   * Returns whether more tokens are available
   *
   * @return bool
   */
  public function hasNext() {
    return $this->tokens->valid();
  }
  /**
   * Returns next token
   *
   * @param  string $delim If passed, uses the delimiters instead of the global ones.
   * @return string
   */
  public function next() {
    if ($this->current) {
      $token= $this->current;
      $this->current= null;
    } else {
      $token= $this->tokens->current();
      $this->tokens->next();
    }
    // echo "< ", $token, "\n";
    return $token;
  }
}
