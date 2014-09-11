<?php namespace text\json;

use io\streams\InputStream;
use text\Tokenizer;
use lang\IllegalStateException;

class JsonTokenizer extends Tokenizer {

  public function __construct(InputStream $in) {
    $this->in= $in;
    $this->bytes= '';
    $this->pos= 0;
    $this->len= 0;
  }

  public function reset() {
    // TBI
  }

  public function pushBack($bytes) {
    if ($this->pos < 0) return;
    $this->bytes= $bytes.substr($this->bytes, $this->pos);
    $this->pos= 0;
    $this->len= strlen($this->bytes);
  }

  public function hasMoreTokens() {
    return $this->pos >= 0;
  }

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
