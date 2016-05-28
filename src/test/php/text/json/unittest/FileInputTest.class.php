<?php namespace text\json\unittest;

use text\json\FileInput;
use io\File;
use io\Path;
use lang\System;

/**
 * Tests the FileInput implementation
 */
class FileInputTest extends JsonInputTest {

  /** @param io.Path */
  private function tempName() {
    return Path::compose([System::tempDir(), md5(uniqid()).'-xp.json']);
  }

  /**
   * Returns a file with a given source opened in the given mode
   *
   * @param  string $source
   * @param  string $mode One of the file open modes
   * @return io.File
   */
  private function fileWith($source, $mode) {
    $file= new File($this->tempName());
    $file->open($mode);
    $file->write($source);
    return $file;
  }

  /**
   * Returns the  implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.Input
   */
  protected function input($source, $encoding= 'utf-8') {
    $file= $this->fileWith($source, File::REWRITE);
    $file->seek(0, SEEK_SET);
    return new FileInput($file, $encoding);
  }

  #[@test]
  public function can_create_with_file() {
    $file= new File($this->tempName());
    $file->touch();
    $this->assertEquals($file, (new FileInput($file))->file());
  }

  #[@test]
  public function can_create_with_string() {
    $name= $this->tempName();
    touch($name);
    $this->assertEquals(new File($name), (new FileInput($name))->file());
  }

  #[@test]
  public function is_closed_after_reading() {
    $file= $this->fileWith('"test"', File::WRITE);
    $file->close();
    (new FileInput($file))->read();
    $this->assertFalse($file->isOpen());
  }

  #[@test]
  public function is_closed_after_elements() {
    $file= $this->fileWith('[]', File::WRITE);
    $file->close();
    iterator_to_array((new FileInput($file))->elements());
    $this->assertFalse($file->isOpen());
  }

  #[@test]
  public function is_closed_after_pairs() {
    $file= $this->fileWith('{}', File::WRITE);
    $file->close();
    iterator_to_array((new FileInput($file))->pairs());
    $this->assertFalse($file->isOpen());
  }

  #[@test]
  public function open_files_are_not_closed() {
    $file= $this->fileWith('{}', File::REWRITE);
    $file->seek(0, SEEK_SET);
    (new FileInput($file))->read();
    $this->assertTrue($file->isOpen());
  }
}