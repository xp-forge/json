<?php namespace text\json\unittest;

use lang\FormatException;
use text\json\{Format, Json, StringInput, StringOutput};
use test\Assert;
use test\{Expect, Test};

class JsonTest {

  #[Test]
  public function read_input() {
    Assert::equals('Test', Json::read(new StringInput('"Test"')));
  }

  #[Test]
  public function read_string() {
    Assert::equals('Test', Json::read('"Test"'));
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
  public function of_string() {
    Assert::equals('"Test"', Json::of('Test'));
  }

  #[Test]
  public function of_string_with_format() {
    Assert::equals('"Test"', Json::of('Test', Format::$DEFAULT));
  }
}