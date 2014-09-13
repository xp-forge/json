<?php namespace text\json;

abstract class Stream extends \lang\Object implements \lang\Closeable {
  protected $out;

  public function __construct(Output $out) {
    $this->out= $out;
  }

  public abstract function close();
}