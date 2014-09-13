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
   * Writes a given value
   *
   * @param  var $value
   */
  public function write($value) {
    if (is_array($value)) {
      if (empty($value)) {
        $this->file->write('[]');
      } else if (0 === key($value)) {
        $this->file->write('[');
        $next= false;
        foreach ($value as $element) {
          if ($next) {
            $this->file->write(', ');
          } else {
            $next= true;
          }
          $this->file->write($this->representationOf($element));
        }
        $this->file->write(']');
      } else {
        $this->file->write('{');
        $next= false;
        foreach ($value as $key => $mapped) {
          if ($next) {
            $this->file->write(', ');
          } else {
            $next= true;
          }
          $this->file->write($this->representationOf($key).' : '.$this->representationOf($mapped));
        }
        $this->file->write('}');
      }
    } else {
      $this->file->write($this->representationOf($value));
    }
    $this->file->close();
  }

  /** @return io.File */
  public function file() { return $this->file; }
}