<?php namespace text\json;

class ArrayStream extends Stream {

  public function element($element) {
    if ($this->next) {
      $this->out->appendToken(', ');
    } else {
      $this->out->appendToken('[');
      $this->next= true;
    }
    $this->out->appendToken($this->out->format->representationOf($element));
  }

  public function close() {
    $this->out->appendToken(']');
    parent::close();
  }
}