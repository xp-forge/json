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
    $f= $this->out->format;
    if ($this->next) {
      $t= $f->comma;
    } else {
      $t= $f->open('{');
      $this->next= true;
    }
    $this->out->appendToken($t.$f->representationOf($key).$f->colon.$f->representationOf($value));
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