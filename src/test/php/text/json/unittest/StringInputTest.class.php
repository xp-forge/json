<?php namespace text\json\unittest;

use text\json\StringInput;
use test\Assert;

/**
 * Tests the JsonString implementation
 */
class StringInputTest extends JsonInputTest {

  /**
   * Returns the implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.Input
   */
  protected function input($source, $encoding= 'utf-8') {
    return new StringInput($source, $encoding);
  }
}