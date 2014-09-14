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
 * @test  xp://text.json.unittest.StringOutputTest
 */
class StringOutput extends Output {
  protected $bytes= null;

  /**
   * Writes a given value
   *
   * @param  var $value
   */
  public function write($value) {
    if (null === $this->bytes) {
      $this->bytes= $this->format->representationOf($value);
    } else {
      throw new IllegalStateException('Already written');
    }
  }

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