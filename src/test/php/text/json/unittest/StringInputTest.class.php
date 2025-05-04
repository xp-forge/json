<?php namespace text\json\unittest;

use text\json\StringInput;

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