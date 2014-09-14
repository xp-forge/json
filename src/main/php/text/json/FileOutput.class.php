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
   * @param  text.json.Format $format
   */
  public function __construct(File $out, Format $format= null) {
    parent::__construct($format);
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

  /** @return void */
  public function close() { $this->file->isOpen() && $this->file->close(); }
}