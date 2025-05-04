<?php namespace text\json\unittest;

use test\{Assert, Test};
use text\json\DenseFormat;

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
    Assert::equals('[1]', $this->format()->representationOf([1]));
  }

  #[Test]
  public function array_with_multiple_elements() {
    Assert::equals('[1,2,3]', $this->format()->representationOf([1, 2, 3]));
  }

  #[Test]
  public function object_with_one_pair() {
    Assert::equals('{"key":"value"}', $this->format()->representationOf(['key' => 'value']));
  }

  #[Test]
  public function object_with_multiple_pairs() {
    Assert::equals('{"a":"v1","b":"v2"}', $this->format()->representationOf(['a' => 'v1', 'b' => 'v2']));
  }
}