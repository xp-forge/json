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
      $this->bytes= $this->representationOf($value);
    } else {
      throw new IllegalStateException('Already written');
    }
  }

  /** @return string */
  public function bytes() { return $this->bytes; }

}