<?php namespace text\json\unittest;

use text\json\WrappedFormat;

class WrappedFormatTest extends FormatTest {

  /** @return text.json.Format */
  protected function format() { return new WrappedFormat('  '); }

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
    $this->assertEquals(
      "{\n  \"key\" : \"value\"\n}",
      $this->format()->representationOf(['key' => 'value'])
    );
  }

  #[@test]
  public function object_with_multiple_pairs() {
    $this->assertEquals(
      "{\n  \"a\" : \"v1\",\n  \"b\" : \"v2\"\n}",
      $this->format()->representationOf(['a' => 'v1', 'b' => 'v2'])
    );
  }

  #[@test]
  public function object_with_nested_objects() {
    $this->assertEquals(
      "{\n  \"a\" : \"v1\",\n  \"b\" : {\n    \"v2\" : {\n      \"key\" : \"value\"\n    }\n  }\n}",
      $this->format()->representationOf(['a' => 'v1', 'b' => ['v2' => ['key' => 'value']]])
    );
  }
}