<?php namespace text\json\unittest;

use text\json\JsonStringReader;

/**
 * Tests the JsonStringReader implementation
 */
class JsonStringReaderTest extends JsonReaderTest {

  /**
   * Returns the reader implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.JsonReader
   */
  protected function reader($source, $encoding= 'utf-8') {
    return new JsonStringReader($source, $encoding);
  }
}