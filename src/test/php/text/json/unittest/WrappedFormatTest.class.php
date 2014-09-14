<?php namespace text\json\unittest;

use text\json\WrappedFormat;

class WrappedFormatTest extends FormatTest {

  /**
   * Returns a `Format` instance
   *
   * @param  int $options
   * @return text.json.Format
   */
  protected function format($options= 0) {
    return new WrappedFormat('  ', $options);
  }

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

  #[@test]
  public function open_and_close_an_array_with_multiple_elements_and_nested_object() {
    $format= $this->format();
    $repr= '';
    $repr.= $format->open('[');
    $repr.= $format->representationOf('a');
    $repr.= $format->comma;
    $repr.= $format->representationOf(['v2' => ['key' => 'value']]);
    $repr.= $format->comma;
    $repr.= $format->representationOf('b');
    $repr.= $format->close(']');
    $this->assertEquals(
      "[\n  \"a\",\n  {\n    \"v2\" : {\n      \"key\" : \"value\"\n    }\n  },\n  \"b\"\n]",
      $repr
    );
  }

  #[@test]
  public function open_and_close_an_object_with_multiple_pairs_and_nested_object() {
    $format= $this->format();
    $repr= '';
    $repr.= $format->open('{');
    $repr.= $format->representationOf('a');
    $repr.= $format->colon;
    $repr.= $format->representationOf('v1');
    $repr.= $format->comma;
    $repr.= $format->representationOf('b');
    $repr.= $format->colon;
    $repr.= $format->representationOf(['v2' => ['key' => 'value']]);
    $repr.= $format->close('}');
    $this->assertEquals(
      "{\n  \"a\" : \"v1\",\n  \"b\" : {\n    \"v2\" : {\n      \"key\" : \"value\"\n    }\n  }\n}",
      $repr
    );
  }
}