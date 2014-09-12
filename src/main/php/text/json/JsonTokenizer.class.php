<?php namespace text\json;

use io\streams\InputStream;
use text\Tokenizer;
use lang\IllegalStateException;

class JsonTokenizer extends Tokenizer {
  protected $in;
  protected $bytes;
  protected $pos;
  protected $len;

  /**
   * Creates a new instance
   *
   * @param  io.streams.InputStream $in
   */
  public function __construct(InputStream $in) {
    $this->in= $in;
    $this->bytes= '';
    $this->pos= 0;
    $this->len= 0;
  }

  /**
   * Resets tokenizer
   *
   * @return void
   */
  public function reset() {
    // TBI
  }

  /**
   * Pushes back a given byte sequence to be retokenized
   *
   * @param  string $bytes
   * @return void
   */
  public function pushBack($bytes) {
    if ($this->pos < 0) return;
    $this->bytes= $bytes.substr($this->bytes, $this->pos);
    $this->pos= 0;
    $this->len= strlen($this->bytes);
  }

  /**
   * Returns whether more tokens are available
   *
   * @return bool
   */
  public function hasMoreTokens() {
    return $this->pos >= 0;
  }

  /**
   * Returns next token
   *
   * @param  string $delim If passed, uses the delimiters instead of the global ones.
   * @return string
   */
  public function nextToken($delim= null) {
    if ($this->pos < 0) return null;

    do {
      if (0 === $this->len) {
        $token= null;
      } else {
        $s= strcspn($this->bytes, $delim ?: "{[,\"]}: \r\n\t", $this->pos);
        if (0 === $s) {
          $token= $this->bytes{$this->pos};
          $this->pos++;
        } else {
          $token= substr($this->bytes, $this->pos, $s);
          $this->pos+= $s;
        }
      }

      if ($this->pos < $this->len) {
        return $token;
      } else if ($this->in->available()) {
        $this->bytes= $token.$this->in->read();
        $this->pos= 0;
        $this->len= strlen($this->bytes);
        continue;
      } else {
        $this->pos= -1;
        return $token;
      }
    } while (true);

    throw new IllegalStateException('Unreachable');
  }
}
