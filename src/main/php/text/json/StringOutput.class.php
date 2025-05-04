<?php namespace text\json;

use io\streams\OutputStream;

/**
 * Writes JSON to a string
 *
 * ```php
 * $json= new StringOutput();
 * $json->write('Hello World');
 * echo $json->bytes();
 * ```
 *
 * @test  text.json.unittest.StringOutputTest
 */
class StringOutput extends Output {
  protected $bytes= null;

  /**
   * Append a token
   *
   * @param  string $bytes
   * @return void
   */
  public function appendToken($bytes) {
    $this->bytes.= $bytes;
  }

  /** @return string */
  public function bytes() { return $this->bytes; }

}