<?php namespace text\json\unittest;

use io\File;
use io\Path;
use lang\System;
use text\json\FileOutput;
use text\json\Types;

class FileOutputTest extends JsonOutputTest {

  /** @param io.Path */
  private function tempName() {
    return Path::compose([System::tempDir(), md5(uniqid()).'-xp.json']);
  }

  /** @return text.json.Output */
  protected function output() {
    $file= new File($this->tempName());
    $file->open(FILE_MODE_REWRITE);
    return new FileOutput($file);
  }

  /**
   * Returns the result
   *
   * @param  text.json.Output $out
   * @return string
   */
  protected function result($out) {
    $file= $out->file();
    $file->seek(0, SEEK_SET);
    return $file->read($file->size());
  }

  #[@test]
  public function can_create_with_file() {
    $file= new File($this->tempName());
    $this->assertEquals($file, (new FileOutput($file))->file());
  }

  #[@test]
  public function can_create_with_string() {
    $name= $this->tempName();
    $this->assertEquals(new File($name), (new FileOutput($name))->file());
  }

  #[@test]
  public function is_closed_after_writing() {
    $file= new File($this->tempName());
    (new FileOutput($file))->write('test');
    $this->assertFalse($file->isOpen());
  }

  #[@test]
  public function is_closed_after_begin_array() {
    $file= new File($this->tempName());
    (new FileOutput($file))->begin(Types::$ARRAY)->close();
    $this->assertFalse($file->isOpen());
  }

  #[@test]
  public function is_closed_after_begin_object() {
    $file= new File($this->tempName());
    (new FileOutput($file))->begin(Types::$OBJECT)->close();
    $this->assertFalse($file->isOpen());
  }

  #[@test]
  public function open_files_are_not_closed() {
    $file= new File($this->tempName());
    $file->open(FILE_MODE_REWRITE);
    $file->seek(0, SEEK_SET);
    (new FileOutput($file))->write('test');
    $this->assertTrue($file->isOpen());
  }
}