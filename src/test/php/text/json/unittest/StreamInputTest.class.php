<?php namespace text\json\unittest;

use io\IOException;
use io\streams\{InputStream, MemoryInputStream};
use text\json\StreamInput;
use unittest\{Test, Values};

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

  #[Test]
  public function read_utf_8_with_bom() {
    $this->assertEquals('Übercoder', $this->read("\357\273\277\"\303\234bercoder\""));
  }

  #[Test]
  public function read_utf_16le_with_bom() {
    $this->assertEquals('Übercoder', $this->read("\377\376\"\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\"\000"));
  }

  #[Test]
  public function read_utf_16be_with_bom() {
    $this->assertEquals('Übercoder', $this->read("\376\377\000\"\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\""));
  }

  #[Test]
  public function detect_utf_16le() {
    $this->assertEquals('Übercoder', $this->read("\"\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\"\000"));
  }

  #[Test]
  public function detect_utf_16be() {
    $this->assertEquals('Übercoder', $this->read("\000\"\000\334\000b\000e\000r\000c\000o\000d\000e\000r\000\""));
  }

  #[Test, Values(["\357\273\277[1]", "\377\376[\0001\000]\000", "\376\377\000[\0001\000]"])]
  public function calling_read_after_resetting_with_bom($input) {
    $input= $this->input($input);
    $this->assertEquals([1], $input->read(), '#1');
    $input->reset();
    $this->assertEquals([1], $input->read(), '#2');
  }

  #[Test, Values(["\357\273\277[1]", "\377\376[\0001\000]\000", "\376\377\000[\0001\000]"])]
  public function calling_elements_after_resetting_with_bom($input) {
    $input= $this->input($input);
    $this->assertEquals([1], iterator_to_array($input->elements()), '#1');
    $input->reset();
    $this->assertEquals([1], iterator_to_array($input->elements()), '#2');
  }

  #[Test, Values(["\357\273\277{\"key\":\"value\"}", "\377\376{\000\"\000k\000e\000y\000\"\000:\000\"\000v\000a\000l\000u\000e\000\"\000}\000", "\376\377\000{\000\"\000k\000e\000y\000\"\000:\000\"\000v\000a\000l\000u\000e\000\"\000}"])]
  public function calling_pairs_after_resetting_with_bom($input) {
    $input= $this->input($input);
    $this->assertEquals(['key' => 'value'], iterator_to_array($input->pairs()), '#1');
    $input->reset();
    $this->assertEquals(['key' => 'value'], iterator_to_array($input->pairs()), '#2');
  }

  #[Test]
  public function cannot_reset_unseekable() {
    $input= new StreamInput(newinstance(InputStream::class, [], [
      'read'      => function($size= 8192) { return '[1]'; },
      'available' => function() { return true; },
      'close'     => function() { }
    ]));
    $this->assertEquals([1], iterator_to_array($input->elements()), '#1');
    try {
      $input->reset();
      iterator_to_array($input->elements());
      $this->fail('Expected exception not caught', null, 'io.IOException');
    } catch (IOException $expected) {
      // OK
    }
  }

  #[Test]
  public function string_representation() {
    $this->assertEquals(
      'text.json.StreamInput(stream= io.streams.MemoryInputStream(@2 of 2 bytes), encoding= utf-8)',
      $this->input('{}')->toString()
    );
  }
}