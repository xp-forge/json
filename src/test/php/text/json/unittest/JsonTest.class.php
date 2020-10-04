<?php namespace text\json\unittest;

use lang\FormatException;
use text\json\{Format, Json, StringInput, StringOutput};
use unittest\{Expect, Test};

class JsonTest extends \unittest\TestCase {

  #[Test]
  public function read_input() {
    $this->assertEquals('Test', Json::read(new StringInput('"Test"')));
  }

  #[Test]
  public function read_string() {
    $this->assertEquals('Test', Json::read('"Test"'));
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
    $this->assertEquals('"Test"', Json::write('Test', new StringOutput())->bytes());
  }

  #[Test]
  public function of_string() {
    $this->assertEquals('"Test"', Json::of('Test'));
  }

  #[Test]
  public function of_string_with_format() {
    $this->assertEquals('"Test"', Json::of('Test', Format::$DEFAULT));
  }
}