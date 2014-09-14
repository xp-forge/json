<?php namespace text\json;

class ObjectStream extends Stream {

  /**
   * Writes a key/value pair
   *
   * @param  string $key
   * @param  var $value
   * @return void
   */
  public function pair($key, $value) {
    if ($this->next) {
      $t= $this->out->format->comma;
    } else {
      $t= $this->out->format->open('{');
      $this->next= true;
    }
    $f= $this->out->format;
    $this->out->appendToken($t.$f->representationOf($key).$this->out->format->colon.$f->representationOf($value));
  }

  /**
   * Closes this stream
   *
   * @return void
   */
  public function close() {
    $this->out->appendToken($this->out->format->close('}'));
    parent::close();
  }
}