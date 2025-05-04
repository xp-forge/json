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
 * @test  text.json.unittest.FileOutputTest
 */
class FileOutput extends Output {
  protected $file, $wasOpen;

  /**
   * Creates a new instance
   *
   * @param  string|io.File $target
   * @param  ?text.json.Format $format
   */
  public function __construct($target, $format= null) {
    parent::__construct($format);
    if ($target instanceof File) {
      $this->file= $target;
      $this->wasOpen= $this->file->isOpen();
    } else {
      $this->file= new File($target);
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