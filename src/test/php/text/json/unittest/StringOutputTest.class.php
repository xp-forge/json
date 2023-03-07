<?php namespace text\json\unittest;

use text\json\StringOutput;
use test\Assert;

class StringOutputTest extends JsonOutputTest {

  /** @return text.json.Output */
  protected function output() { return new StringOutput(); }

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