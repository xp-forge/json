<?php namespace text\json;

use io\File;

/**
 * Writes JSON to a given output stream
 *
 * ```php
 * $json= new FileOutput(new File('input.json'));
 * $json->write('Hello World');
 * ```
 *
 * @test  xp://text.json.unittest.StreamOutputTest
 */
class FileOutput extends StreamOutput {

  /**
   * Creates a new instance
   *
   * @param  io.File $out
   * @param  string $encoding
   */
  public function __construct(File $out, $encoding= \xp::ENCODING) {
    parent::__construct($out->getOutputStream(), $encoding);
  }
}