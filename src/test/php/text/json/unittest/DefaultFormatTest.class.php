<?php namespace text\json\unittest;

use text\json\DefaultFormat;

class DefaultFormatTest extends FormatTest {

  /** @return text.json.Format */
  protected function format() { return new DefaultFormat(); }

  #[@test]
  public function array_with_one_element() {
    $this->assertEquals('[1]', $this->format()->representationOf([1]));
  }

  #[@test]
  public function array_with_multiple_elements() {
    $this->assertEquals('[1, 2, 3]', $this->format()->representationOf([1, 2, 3]));
  }

  #[@test]
  public function object_with_one_pair() {
    $this->assertEquals('{"key" : "value"}', $this->format()->representationOf(['key' => 'value']));
  }

  #[@test]
  public function object_with_multiple_pairs() {
    $this->assertEquals('{"a" : "v1", "b" : "v2"}', $this->format()->representationOf(['a' => 'v1', 'b' => 'v2']));
  }
}