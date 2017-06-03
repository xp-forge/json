<?php namespace text\json;

abstract class Stream implements \lang\Closeable {
  protected $out;

  public function __construct(Output $out) {
    $this->out= $out;
  }

  /** @return void */
  public function close() {
    $this->out->close();
  }
}