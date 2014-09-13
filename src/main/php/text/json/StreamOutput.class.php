<?php namespace text\json;

use io\streams\OutputStream;

/**
 * Writes JSON to a given input stream
 *
 * ```php
 * $json= new StreamOutput((new File('input.json'))->getOutputStream()));
 * $json->write('Hello World');
 * ```
 *
 * @test  xp://text.json.unittest.StreamOutputTest
 */
class StreamOutput extends Output {
  protected $out;

  /**
   * Creates a new instance
   *
   * @param  io.streams.InputStream $out
   * @param  string $encoding
   */
  public function __construct(OutputStream $out, $encoding= \xp::ENCODING) {
    parent::__construct($encoding);
    $this->out= $out;
  }

  /**
   * Writes a given value
   *
   * @param  var $value
   */
  public function write($value) {
    $this->out->write($this->representationOf($value));
    $this->out->close();
  }
}