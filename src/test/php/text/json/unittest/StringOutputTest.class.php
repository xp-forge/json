<?php namespace text\json\unittest;

use text\json\StringOutput;

class StringOutputTest extends JsonOutputTest {

  /**
   * Returns the implementation
   *
   * @param  string $encoding
   * @return text.json.Output
   */
  protected function output($encoding= 'utf-8') {
    return new StringOutput();
  }

  /**
   * Returns the result
   *
   * @param  text.json.Output $out
   * @return string
   */
  protected function result($out) {
    return $out->bytes();
  }
}