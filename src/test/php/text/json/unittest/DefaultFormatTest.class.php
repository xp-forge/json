<?php namespace text\json\unittest;

use text\json\Format;

class DefaultFormatTest extends \unittest\TestCase {

  #[@test]
  public function string() {
    $this->assertEquals('"Test"', Format::$DEFAULT->representationOf('Test'));
  }

  #[@test]
  public function int() {
    $this->assertEquals('0', Format::$DEFAULT->representationOf(0));
  }

  #[@test]
  public function double() {
    $this->assertEquals('0.0', Format::$DEFAULT->representationOf(0.0));
  }

  #[@test]
  public function true() {
    $this->assertEquals('true', Format::$DEFAULT->representationOf(true));
  }

  #[@test]
  public function false() {
    $this->assertEquals('false', Format::$DEFAULT->representationOf(false));
  }

  #[@test]
  public function null() {
    $this->assertEquals('null', Format::$DEFAULT->representationOf(null));
  }

  #[@test]
  public function empty_array() {
    $this->assertEquals('[]', Format::$DEFAULT->representationOf([]));
  }

  #[@test]
  public function int_array() {
    $this->assertEquals('[1, 2, 3]', Format::$DEFAULT->representationOf([1, 2, 3]));
  }

  #[@test]
  public function object() {
    $this->assertEquals('{"a" : "v1", "b" : "v2"}', Format::$DEFAULT->representationOf(['a' => 'v1', 'b' => 'v2']));
  }
}