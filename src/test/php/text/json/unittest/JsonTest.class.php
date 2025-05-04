<?php namespace text\json\unittest;

use io\streams\{MemoryInputStream, MemoryOutputStream};
use io\{Files, TempFile};
use lang\{FormatException, IllegalArgumentException};
use test\{Assert, Expect, Test};
use text\json\{Format, Json, StringInput, StringOutput};
use util\Bytes;

class JsonTest {

  #[Test]
  public function read_input() {
    Assert::equals('Test', Json::read(new StringInput('"Test"')));
  }

  #[Test]
  public function read_string() {
    Assert::equals('Test', Json::read('"Test"'));
  }

  #[Test]
  public function read_casts_input_to_string() {
    Assert::equals('Test', Json::read(new Bytes('"Test"')));
  }

  #[Test]
  public function read_file() {
    $file= new TempFile();
    try {
      Assert::equals('Test', Json::read($file->containing('"Test"')));
    } finally {
      $file->unlink();
    }
  }

  #[Test]
  public function read_stream() {
    Assert::equals('Test', Json::read(new MemoryInputStream('"Test"')));
  }

  #[Test, Expect(FormatException::class)]
  public function read_malformed_string() {
    Json::read('this.is.not.json');
  }

  #[Test, Expect(FormatException::class)]
  public function read_malformed_input() {
    Json::read(new StringInput('this.is.not.json'));
  }

  #[Test]
  public function write_output() {
    Assert::equals('"Test"', Json::write('Test', new StringOutput())->bytes());
  }

  #[Test]
  public function write_file() {
    $file= new TempFile();
    try {
      Assert::equals('"Test"', Files::read(Json::write('Test', $file)->file()));
    } finally {
      $file->unlink();
    }
  }

  #[Test]
  public function write_stream() {
    Assert::equals('"Test"', Json::write('Test', new MemoryOutputStream())->stream()->bytes());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function write_incorrect_type() {
    Json::write('Test', $this);
  }

  #[Test]
  public function of_string() {
    Assert::equals('"Test"', Json::of('Test'));
  }

  #[Test]
  public function of_string_with_format() {
    Assert::equals('"Test"', Json::of('Test', Format::$DEFAULT));
  }

  #[Test]
  public function object_roundtrip() {
    Assert::equals('{}', Json::of(Json::read('{}')));
  }
}