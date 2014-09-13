<?php namespace text\json\unittest;

use text\json\StreamInput;
use io\streams\MemoryInputStream;

/**
 * Tests the StreamInput implementation
 */
class StreamInputTest extends JsonInputTest {

  /**
   * Returns the  implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.Input
   */
  protected function input($source, $encoding= 'utf-8') {
    return new StreamInput(new MemoryInputStream($source), $encoding);
  }

  #[@test]
  public function read_utf_8_with_bom() {
    $this->assertEquals('Übercoder', $this->read("\357\273\277\"\303\234bercoder\""));
  }

  #[@test]
  public function read_utf_16le_with_bom() {
    $this->assertEquals('Übercoder', $this->read("\377\376\"\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\"\000"));
  }

  #[@test]
  public function read_utf_16be_with_bom() {
    $this->assertEquals('Übercoder', $this->read("\376\377\000\"\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\""));
  }
}