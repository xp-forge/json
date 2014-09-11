<?php namespace text\json\unittest;

use text\json\JsonStreamReader;
use io\streams\MemoryInputStream;

/**
 * Tests the JsonStreamReader implementation
 */
class JsonStreamReaderTest extends JsonReaderTest {

  /**
   * Returns the reader implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.JsonReader
   */
  protected function reader($source, $encoding= 'utf-8') {
    return new JsonStreamReader(new MemoryInputStream($source), $encoding);
  }
}