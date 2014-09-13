<?php namespace text\json;

use io\File;

/**
 * Reads JSON from a given input stream
 *
 * ```php
 * $json= new JsonFile(new File('input.json'));
 * $value= $json->read();
 * ```
 */
class JsonFile extends JsonStream {
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