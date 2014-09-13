<?php namespace text\json\unittest;

use text\json\JsonString;

/**
 * Tests the JsonString implementation
 */
class JsonStringTest extends JsonInputTest {

  /**
   * Returns the  implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.Input
   */
  protected function input($source, $encoding= 'utf-8') {
    return new JsonString($source, $encoding);
  }
}