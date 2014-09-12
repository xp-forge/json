<?php namespace text\json\unittest;

use text\json\JsonStream;
use io\streams\MemoryInputStream;

/**
 * Tests the JsonStream implementation
 */
class JsonStreamTest extends JsonInputTest {

  /**
   * Returns the  implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.Input
   */
  protected function input($source, $encoding= 'utf-8') {
    return new JsonStream(new MemoryInputStream($source), $encoding);
  }
}