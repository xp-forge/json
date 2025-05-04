<?php namespace text\json;

use lang\Closeable;

abstract class Stream implements Closeable {
  protected $out;

  /** Creates a new instance */
  public function __construct(Output $out) {
    $this->out= $out;
  }

  /** @return void */
  public function close() {
    $this->out->close();
  }
}