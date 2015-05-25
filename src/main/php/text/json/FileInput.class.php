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
  protected $file, $wasOpen;

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
    $this->wasOpen || $this->file->open(FILE_MODE_READ);
    parent::__construct($this->file->getInputStream(), $encoding);
  }

  /** @return void */
  public function close() {
    if (!$this->wasOpen && $this->file->isOpen()) {
      $this->file->close();
    }
  }

  /** @return io.File */
  public function file() { return $this->file; }
}