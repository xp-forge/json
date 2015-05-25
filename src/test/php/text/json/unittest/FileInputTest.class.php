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
   * Returns the  implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.Input
   */
  protected function input($source, $encoding= 'utf-8') {
    $file= new File($this->tempName());
    $file->open(FILE_MODE_REWRITE);
    $file->write($source);
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
}