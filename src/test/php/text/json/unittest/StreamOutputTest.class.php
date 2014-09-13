<?php namespace text\json\unittest;

use io\streams\MemoryOutputStream;
use text\json\StreamOutput;

class StreamOutputTest extends JsonOutputTest {

  /**
   * Helper
   *
   * @param  var $data
   * @param  string $encoding
   * @return string
   */
  protected function write($data, $encoding= 'utf-8') {
    $stream= new MemoryOutputStream();
    $out= new StreamOutput($stream, $encoding);
    $out->write($data);
    return $stream->getBytes();
  }
}