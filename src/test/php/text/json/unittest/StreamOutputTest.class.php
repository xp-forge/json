<?php namespace text\json\unittest;

use io\streams\MemoryOutputStream;
use text\json\StreamOutput;

class StreamOutputTest extends JsonOutputTest {

  /**
   * Returns the implementation
   *
   * @param  string $encoding
   * @return text.json.Output
   */
  protected function output($encoding= 'utf-8') {
    return new StreamOutput(new MemoryOutputStream());
  }

  /**
   * Returns the result
   *
   * @param  text.json.Output $out
   * @return string
   */
  protected function result($out) {
    return $out->stream()->getBytes();
  }
}