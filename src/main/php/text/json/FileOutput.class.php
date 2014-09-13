<?php namespace text\json;

use io\File;

/**
 * Writes JSON to a given file
 *
 * ```php
 * $json= new FileOutput(new File('output.json'));
 * $json->write('Hello World');
 * ```
 */
class FileOutput extends Output {
  protected $file;

  /**
   * Creates a new instance
   *
   * @param  io.File $out
   * @param  string $encoding
   */
  public function __construct(File $out, $encoding= \xp::ENCODING) {
    parent::__construct($encoding);
    $this->file= $out;
    $this->file->open(FILE_MODE_WRITE);
  }

  /**
   * Append a token
   *
   * @param  string $bytes
   * @return void
   */
  public function appendToken($bytes) {
    $this->file->write($bytes);
  }

  /** @return io.File */
  public function file() { return $this->file; }
}