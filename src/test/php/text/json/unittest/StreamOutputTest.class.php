<?php namespace text\json\unittest;

use io\streams\MemoryOutputStream;
use text\json\StreamOutput;

class StreamOutputTest extends JsonOutputTest {

  /** @return text.json.Output */
  protected function output() { return new StreamOutput(new MemoryOutputStream()); }

  /**
   * Returns the result
   *
   * @param  text.json.Output $out
   * @return string
   */
  protected function result($out) {
    return $out->stream()->bytes();
  }

  #[Test]
  public function string_representation() {
    $this->assertEquals(
      'text.json.StreamOutput(stream= io.streams.MemoryOutputStream(@0 of 0 bytes), format= text.json.DenseFormat)',
      $this->output()->toString()
    );
  }
}