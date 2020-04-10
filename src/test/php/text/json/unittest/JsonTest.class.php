<?php namespace text\json\unittest;

use lang\FormatException;
use text\json\{Format, Json, StringInput, StringOutput};

class JsonTest extends \unittest\TestCase {

  #[@test]
  public function read_input() {
    $this->assertEquals('Test', Json::read(new StringInput('"Test"')));
  }

  #[@test]
  public function read_string() {
    $this->assertEquals('Test', Json::read('"Test"'));
  }

  #[@test, @expect(FormatException::class)]
  public function read_malformed_string() {
    Json::read('this.is.not.json');
  }

  #[@test, @expect(FormatException::class)]
  public function read_malformed_input() {
    Json::read(new StringInput('this.is.not.json'));
  }

  #[@test]
  public function write_output() {
    $this->assertEquals('"Test"', Json::write('Test', new StringOutput())->bytes());
  }

  #[@test]
  public function of_string() {
    $this->assertEquals('"Test"', Json::of('Test'));
  }

  #[@test]
  public function of_string_with_format() {
    $this->assertEquals('"Test"', Json::of('Test', Format::$DEFAULT));
  }
}