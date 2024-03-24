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
   * @param  var $arg Either an io.File object or a file name
   * @param  ?text.json.Format $format
   */
  public function __construct($arg, $format= null) {
    parent::__construct($format);
    if ($arg instanceof File) {
      $this->file= $arg;
      $this->wasOpen= $this->file->isOpen();
    } else {
      $this->file= new File($arg);
      $this->wasOpen= false;
    }
    $this->wasOpen || $this->file->open(File::WRITE);
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

  /** @return void */
  public function close() {
    if (!$this->wasOpen && $this->file->isOpen()) {
      $this->file->close();
    }
  }

  /** @return io.File */
  public function file() { return $this->file; }

  /** @return string */
  public function toString() {
    return nameof($this).'(file= '.$this->file->toString().', format= '.$this->format->toString().')';
  }
}