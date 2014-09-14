<?php namespace text\json;

class ArrayStream extends Stream {

  /**
   * Writes an element
   *
   * @param  var $element
   * @return void
   */
  public function element($element) {
    if ($this->next) {
      $this->out->appendToken($this->out->format->comma);
    } else {
      $this->out->appendToken($this->out->format->open('['));
      $this->next= true;
    }
    $this->out->appendToken($this->out->format->representationOf($element));
  }

  /**
   * Closes this stream
   *
   * @return void
   */
  public function close() {
    $this->out->appendToken($this->out->format->close(']'));
    parent::close();
  }
}