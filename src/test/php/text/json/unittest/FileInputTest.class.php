<?php namespace text\json\unittest;

use io\{File, Path};
use lang\Environment;
use test\{Assert, After, Test};
use text\json\FileInput;

/**
 * Tests the FileInput implementation
 */
class FileInputTest extends JsonInputTest {
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

  #[Test]
  public function can_create_with_file() {
    $file= new File($this->tempName());
    $file->touch();
    Assert::equals($file, (new FileInput($file))->file());
  }

  #[Test]
  public function can_create_with_string() {
    $name= $this->tempName();
    touch($name);
    Assert::equals(new File($name), (new FileInput($name))->file());
  }

  #[Test]
  public function is_closed_after_reading() {
    $file= $this->fileWith('"test"', File::WRITE);
    $file->close();
    (new FileInput($file))->read();
    Assert::false($file->isOpen());
  }

  #[Test]
  public function is_closed_after_elements() {
    $file= $this->fileWith('[]', File::WRITE);
    $file->close();
    iterator_to_array((new FileInput($file))->elements());
    Assert::false($file->isOpen());
  }

  #[Test]
  public function is_closed_after_pairs() {
    $file= $this->fileWith('{}', File::WRITE);
    $file->close();
    iterator_to_array((new FileInput($file))->pairs());
    Assert::false($file->isOpen());
  }

  #[Test]
  public function open_files_are_not_closed() {
    $file= $this->fileWith('{}', File::REWRITE);
    $file->seek(0, SEEK_SET);
    (new FileInput($file))->read();
    Assert::true($file->isOpen());
  }

  #[Test]
  public function is_reopened_when_reset() {
    $file= $this->fileWith('{}', File::WRITE);
    $file->close();
    $input= new FileInput($file);
    $input->read();
    $input->reset();
    Assert::true($file->isOpen());
  }

  #[Test]
  public function string_representation() {
    $file= $this->fileWith('{}', File::REWRITE);
    Assert::equals(
      'text.json.FileInput(file= '.$file->toString().', encoding= utf-8)',
      (new FileInput($file))->toString()
    );
  }
}