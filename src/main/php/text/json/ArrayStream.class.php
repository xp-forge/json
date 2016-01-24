<?php namespace text\json;

class ArrayStream extends Stream {
  private $next= false;

  /**
   * Writes an element
   *
   * @param  var $element
   * @return void
   */
  public function element($element) {
    $f= $this->out->format;
    if ($this->next) {
      $t= $f->comma;
    } else {
      $t= $f->open('[');
      $this->next= true;
    }
    $this->out->appendToken($t.$f->representationOf($element));
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