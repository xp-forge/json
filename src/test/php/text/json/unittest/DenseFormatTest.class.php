<?php namespace text\json\unittest;

use text\json\DenseFormat;
use unittest\Test;

class DenseFormatTest extends FormatTest {

  /**
   * Returns a `Format` instance
   *
   * @param  int $options
   * @return text.json.Format
   */
  protected function format($options= 0) {
    return new DenseFormat($options);
  }

  #[Test]
  public function array_with_one_element() {
    $this->assertEquals('[1]', $this->format()->representationOf([1]));
  }

  #[Test]
  public function array_with_multiple_elements() {
    $this->assertEquals('[1,2,3]', $this->format()->representationOf([1, 2, 3]));
  }

  #[Test]
  public function object_with_one_pair() {
    $this->assertEquals('{"key":"value"}', $this->format()->representationOf(['key' => 'value']));
  }

  #[Test]
  public function object_with_multiple_pairs() {
    $this->assertEquals('{"a":"v1","b":"v2"}', $this->format()->representationOf(['a' => 'v1', 'b' => 'v2']));
  }
}