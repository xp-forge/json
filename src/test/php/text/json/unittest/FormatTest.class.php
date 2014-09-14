<?php namespace text\json\unittest;

abstract class FormatTest extends \unittest\TestCase {

  /** @return text.json.Format */
  protected abstract function format();

  #[@test]
  public function string() {
    $this->assertEquals('"Test"', $this->format()->representationOf('Test'));
  }

  #[@test]
  public function int() {
    $this->assertEquals('0', $this->format()->representationOf(0));
  }

  #[@test]
  public function double() {
    $this->assertEquals('0.0', $this->format()->representationOf(0.0));
  }

  #[@test]
  public function true() {
    $this->assertEquals('true', $this->format()->representationOf(true));
  }

  #[@test]
  public function false() {
    $this->assertEquals('false', $this->format()->representationOf(false));
  }

  #[@test]
  public function null() {
    $this->assertEquals('null', $this->format()->representationOf(null));
  }

  #[@test]
  public function empty_array() {
    $this->assertEquals('[]', $this->format()->representationOf([]));
  }

  #[@test]
  public abstract function array_with_one_element();

  #[@test]
  public abstract function array_with_multiple_elements();

  #[@test]
  public abstract function object_with_one_pair();

  #[@test]
  public abstract function object_with_multiple_pairs();

}