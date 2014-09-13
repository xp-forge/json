<?php namespace text\json\unittest;

use text\json\StringOutput;

class StringOutputTest extends JsonOutputTest {

  /**
   * Helper
   *
   * @param  var $data
   * @param  string $encoding
   * @return string
   */
  protected function write($data, $encoding= 'utf-8') {
    $out= new StringOutput($encoding);
    $out->write($data);
    return $out->bytes();
  }
}