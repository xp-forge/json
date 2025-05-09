<?php namespace text\json\unittest;

use test\{Assert, Test};
use text\json\{StringOutput, WrappedFormat};

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

  #[Test]
  public function array_with_one_element() {
    Assert::equals("[\n  1\n]", $this->format()->representationOf([1]));
  }

  #[Test]
  public function array_with_multiple_elements() {
    Assert::equals("[\n  1,\n  2,\n  3\n]", $this->format()->representationOf([1, 2, 3]));
  }

  #[Test]
  public function array_with_nested_array() {
    Assert::equals("[\n  1,\n  [2, 3]\n]", $this->format()->representationOf([1, [2, 3]]));
  }

  #[Test]
  public function object_with_one_pair() {
    Assert::equals(
      "{\n  \"key\": \"value\"\n}",
      $this->format()->representationOf(['key' => 'value'])
    );
  }

  #[Test]
  public function object_with_multiple_pairs() {
    Assert::equals(
      "{\n  \"a\": \"v1\",\n  \"b\": \"v2\"\n}",
      $this->format()->representationOf(['a' => 'v1', 'b' => 'v2'])
    );
  }

  #[Test]
  public function object_with_nested_objects() {
    Assert::equals(
      "{\n  \"a\": \"v1\",\n  \"b\": {\n    \"v2\": {\n      \"key\": \"value\"\n    }\n  }\n}",
      $this->format()->representationOf(['a' => 'v1', 'b' => ['v2' => ['key' => 'value']]])
    );
  }

  #[Test]
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
    Assert::equals(
      "[\n  \"a\",\n  {\n    \"v2\": {\n      \"key\": \"value\"\n    }\n  },\n  \"b\"\n]",
      $repr
    );
  }

  #[Test]
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
    Assert::equals(
      "{\n  \"a\": \"v1\",\n  \"b\": {\n    \"v2\": {\n      \"key\": \"value\"\n    }\n  }\n}",
      $repr
    );
  }

  #[Test]
  public function writing_to_output_produces_same_representation() {
    $data= [
      '_id'      => 1234,
      'clusters' => [
        ['skills' => [
          '_id'   => 5678,
          'state' => 'REMOVED',
        ]],
        ['skills' => [
          '_id'   => 7890,
          'state' => 'REMOVED',
        ]],
      ],
      'title'    => 'Test'
    ];
    $format= $this->format();

    $out= new StringOutput($format);
    $out->write($data);
    $representation= $out->bytes();

    Assert::equals($format->representationOf($data), $representation);
  }
}