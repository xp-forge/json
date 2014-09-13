<?php namespace text\json;

use io\File;

/**
 * Reads JSON from a given file
 *
 * ```php
 * $json= new FileInput(new File('input.json'));
 * $value= $json->read();
 * ```
 */
class FileInput extends StreamInput {
  protected $in;

  /**
   * Creates a new instance
   *
   * @param  io.File $in
   * @param  string $encoding
   */
  public function __construct(File $in, $encoding= \xp::ENCODING) {
    parent::__construct($in->getInputStream(), $encoding);
  }
}