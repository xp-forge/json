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
      $this->out->appendToken(', ');
    } else {
      $this->out->appendToken('{');
      $this->next= true;
    }
    $f= $this->out->format;
    $this->out->appendToken($f->representationOf($key).' : '.$f->representationOf($value));
  }

  /**
   * Closes this stream
   *
   * @return void
   */
  public function close() {
    $this->out->appendToken('}');
    parent::close();
  }
}