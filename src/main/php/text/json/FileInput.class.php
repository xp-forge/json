<?php namespace text\json;

use io\File;

/**
 * Reads JSON from a given file
 *
 * ```php
 * $json= new FileInput(new File('input.json'));
 * $value= $json->read();
 * ```
 *
 * @test  xp://text.json.unittest.FileInputTest
 */
class FileInput extends StreamInput {
  protected $file;

  /**
   * Creates a new instance
   *
   * @param  var $arg Either an io.File object or a file name
   * @param  string $encoding
   */
  public function __construct($arg, $encoding= \xp::ENCODING) {
    if ($arg instanceof File) {
      $this->file= $arg;
      $this->wasOpen= $this->file->isOpen();
    } else {
      $this->file= new File($arg);
      $this->wasOpen= false;
    }
    parent::__construct($this->file->getInputStream(), $encoding);
  }

  /** @return io.File */
  public function file() { return $this->file; }

  /** @return void */
  public function close() { $this->wasOpen || $this->file->close(); }

  /** @return void */
  public function __destruct() { $this->close(); }
}