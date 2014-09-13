<?php namespace text\json;

class ObjectStream extends Stream {

  public function pair($key, $value) {
    if ($this->next) {
      $this->out->appendToken(', ');
    } else {
      $this->out->appendToken('{');
      $this->next= true;
    }
    $this->out->appendToken($this->out->representationOf($key).' : '.$this->out->representationOf($value));
  }

  public function close() {
    $this->out->appendToken('}');
  }
}