<?php namespace text\json\unittest;

use io\File;
use io\Path;
use lang\System;
use text\json\FileOutput;

class FileOutputTest extends JsonOutputTest {

  /** @param io.Path */
  private function tempName() {
    return Path::compose([System::tempDir(), md5(uniqid()).'-xp.json']);
  }

  /** @return text.json.Output */
  protected function output() {
    return new FileOutput((new File($this->tempName()))->open(FILE_MODE_REWRITE));
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
}