<?php namespace text\json;

use io\File;

/**
 * Writes JSON to a given file
 *
 * ```php
 * $json= new FileOutput(new File('output.json'));
 * $json->write('Hello World');
 * ```
 *
 * @test  xp://text.json.unittest.FileOutputTest
 */
class FileOutput extends Output {
  protected $file, $wasOpen;

  /**
   * Creates a new instance
   *
   * @param  var $arg
   * @param  text.json.Format $format
   */
  public function __construct($arg, Format $format= null) {
    parent::__construct($format);
    if ($arg instanceof File) {
      $this->file= $arg;
      $this->wasOpen= $this->file->isOpen();
    } else {
      $this->file= new File($arg);
      $this->wasOpen= false;
    }
    $this->wasOpen || $this->file->open(FILE_MODE_WRITE);
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
  public function close() { $this->wasOpen || $this->file->close(); }

  /** @return void */
  public function __destruct() { $this->close(); }
}