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
 * @test  text.json.unittest.FileInputTest
 */
class FileInput extends StreamInput {
  protected $file, $wasOpen;

  /**
   * Creates a new instance
   *
   * @param  string|io.File $file
   * @param  string $encoding
   * @param  int $maximumNesting Maximum nesting level, defaults to 512
   */
  public function __construct($file, $encoding= \xp::ENCODING, $maximumNesting= 512) {
    if ($file instanceof File) {
      $this->file= $file;
      $this->wasOpen= $this->file->isOpen();
    } else {
      $this->file= new File($file);
      $this->wasOpen= false;
    }
    $this->wasOpen || $this->file->open(File::READ);
    parent::__construct($this->file->in(), $encoding, $maximumNesting);
  }

  /**
   * Resets input
   *
   * @return void
   * @throws io.IOException If this input cannot be reset
   */
  public function reset() {
    $this->wasOpen || $this->file->open(File::READ);
    parent::reset();
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
    return nameof($this).'(file= '.$this->file->toString().', encoding= '.$this->encoding.')';
  }
}