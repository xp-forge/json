<?php namespace text\json\unittest;

use test\Assert;
use text\json\StringInput;

/**
 * Tests the JsonString implementation
 */
class StringInputTest extends JsonInputTest {

  /**
   * Returns the implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @param  int $maximumNesting
   * @return text.json.Input
   */
  protected function input($source, $encoding= 'utf-8', $maximumNesting= 512) {
    return new StringInput($source, $encoding, $maximumNesting);
  }
}