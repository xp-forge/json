<?php namespace text\json\unittest;

use io\{File, Path};
use lang\Environment;
use test\{Assert, After, Test};
use text\json\{FileOutput, Types};

class FileOutputTest extends JsonOutputTest {
  private $created= [];

  /** @param io.Path */
  private function tempName() {
    return $this->created[]= Path::compose([Environment::tempDir(), md5(uniqid()).'-xp.json']);
  }

  #[After]
  public function tearDown() {
    foreach ($this->created as $path) {
      $path->exists() && $path->asFile()->unlink();
    }
  }

  /** @return text.json.Output */
  protected function output() {
    $file= new File($this->tempName());
    $file->open(File::REWRITE);
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

  #[Test]
  public function can_create_with_file() {
    $file= new File($this->tempName());
    Assert::equals($file, (new FileOutput($file))->file());
  }

  #[Test]
  public function can_create_with_string() {
    $name= $this->tempName();
    Assert::equals(new File($name), (new FileOutput($name))->file());
  }

  #[Test]
  public function is_closed_after_writing() {
    $file= new File($this->tempName());
    (new FileOutput($file))->write('test');
    Assert::false($file->isOpen());
  }

  #[Test]
  public function is_closed_after_begin_array() {
    $file= new File($this->tempName());
    (new FileOutput($file))->begin(Types::$ARRAY)->close();
    Assert::false($file->isOpen());
  }

  #[Test]
  public function is_closed_after_begin_object() {
    $file= new File($this->tempName());
    (new FileOutput($file))->begin(Types::$OBJECT)->close();
    Assert::false($file->isOpen());
  }

  #[Test]
  public function open_files_are_not_closed() {
    $file= new File($this->tempName());
    $file->open(File::REWRITE);
    $file->seek(0, SEEK_SET);
    (new FileOutput($file))->write('test');
    Assert::true($file->isOpen());
  }

  #[Test]
  public function string_representation() {
    $output= $this->output();
    Assert::equals(
      'text.json.FileOutput(file= '.$output->file()->toString().', format= text.json.DenseFormat)',
      $output->toString()
    );
  }
}