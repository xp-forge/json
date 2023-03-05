<?php namespace text\json\unittest;

use io\streams\MemoryInputStream;
use lang\FormatException;
use test\{Assert, Expect, Test, Values};
use text\json\Types;
use util\collections\Pair;

/**
 * Test JSON input
 *
 * @see   php://json_decode
 * @see   https://bugs.php.net/bug.php?id=41504
 * @see   https://bugs.php.net/bug.php?id=45791
 * @see   https://bugs.php.net/bug.php?id=45989
 * @see   https://bugs.php.net/bug.php?id=54484
 * @see   https://github.com/xp-framework/xp-framework/issues/189
 */
abstract class JsonInputTest {

  /**
   * Returns the input implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.Input
   */
  protected abstract function input($source, $encoding= 'utf-8');

  /**
   * Helper
   *
   * @param  string $source
   * @param  string $encoding
   * @return var
   */
  protected function read($source, $encoding= 'utf-8') {
    return $this->input($source, $encoding)->read();
  }

  #[Test, Values([['', '""'], ['\\', '"\\\\"'], ['/', '"\\/"'], ['Test', '"Test"'], ['Test the "west"', '"Test the \"west\""'], ['Test "the" west', '"Test \"the\" west"'], ["Test\x08", '"Test\b"'], ["Test\x0c", '"Test\f"'], ["Test\x0a", '"Test\n"'], ["Test\x0d", '"Test\r"'], ["Test\x09", '"Test\t"'], ["Test\\", '"Test\\\\"'], ["Test\x14", '"Test\u0014"'], ["Test/", '"Test\/"']])]
  public function read_string($expected, $source) {
    Assert::equals($expected, $this->read($source));
  }

  #[Test, Values([['€uro', '"\u20acuro"'], ['€uro', '"\u20ACuro"'], ['€uro', '"€uro"'], ['Übercoder', '"\u00dcbercoder"'], ['Übercoder', '"\u00DCbercoder"'], ['Übercoder', '"Übercoder"'], ['Poop = 💩', '"Poop = \ud83d\udca9"']])]
  public function read_unicode($expected, $source) {
    Assert::equals($expected, $this->read($source));
  }

  #[Test, Expect(FormatException::class), Values(['"\uTEST"', '[ "\uTEST" ]'])]
  public function illegal_unicode($source) {
    $this->read($source);
  }

  #[Test, Expect(FormatException::class), Values(['"\X"', '[ "\x" ]', '["\\', '"\\"'])]
  public function illegal_escape_sequence($source) {
    $this->read($source);
  }

  #[Test, Expect(FormatException::class)]
  public function illegal_encoding() {
    $this->read("\"\xfc\"");
  }

  #[Test]
  public function read_iso_8859_1() {
    Assert::equals('ü', $this->read("\"\xfc\"", 'iso-8859-1'));
  }

  #[Test]
  public function read_iso_8859_15() {
    Assert::equals('ü€', $this->read("\"\xfc\u20ac\"", 'iso-8859-15'));
  }

  #[Test, Expect(class: FormatException::class, message: '/Unclosed string/'), Values(['"', '"abc', '"abc\"',])]
  public function unclosed_string($source) {
    $this->read($source);
  }

  #[Test, Values([[0, '0'], [1, '1'], [-1, '-1']])]
  public function read_integer($expected, $source) {
    Assert::equals($expected, $this->read($source));
  }

  #[Test, Expect(FormatException::class), Values(['00', '01', '-', '-00', '-01', '+', '+00', '+01', 'e', 'E', 'ee', 'EE', '0e', '0E', '.', '..', '0.', '0.e', '0.E', '0e.', '0E.'])]
  public function malformed_numbers($source) {
    $this->read($source);
  }

  #[Test]
  public function read_int_max() {
    $n= PHP_INT_MAX;
    Assert::equals($n, $this->read((string)$n));
  }

  #[Test]
  public function read_int_min() {
    $n= -PHP_INT_MAX -1;
    Assert::equals($n, $this->read((string)$n));
  }

  #[Test, Values([[0.0, '0.0'], [1.0, '1.0'], [0.5, '0.5'], [-1.0, '-1.0'], [-0.5, '-0.5'], [0.0000000001, '0.0000000001'], [9999999999999999999999999999999999999.0, '9999999999999999999999999999999999999'], [-9999999999999999999999999999999999999.0, '-9999999999999999999999999999999999999']])]
  public function read_double($expected, $source) {
    Assert::equals($expected, $this->read($source));
  }

  #[Test, Values([[10.0, '1E1'], [10.0, '1E+1'], [10.0, '1e1'], [10.0, '1e+1'], [-10.0, '-1E1'], [-10.0, '-1E+1'], [-10.0, '-1e1'], [-10.0, '-1e+1'], [0.1, '1E-1'], [0.1, '1e-1'], [-0.1, '-1E-1'], [0.1, '1e-1'], [0.0, '0E0'], [0.0, '0e0'], [1000000.0, '1E6'], [1000000.0, '1e6'], [-1000000.0, '-1E6'], [-1000000.0, '-1e6']])]
  public function read_exponent($expected, $source) {
    Assert::equals($expected, $this->read($source));
  }

  #[Test, Values([[true, 'true'], [false, 'false'], [null, 'null']])]
  public function read_keyword($expected, $source) {
    Assert::equals($expected, $this->read($source));
  }

  #[Test, Values(['{}', '{ }'])]
  public function read_empty_object($source) {
    Assert::equals([], $this->read($source));
  }

  #[Test, Values(['{"key": "value"}', '{"key" : "value"}', '{ "key" : "value" }'])]
  public function read_key_value_pair($source) {
    Assert::equals(['key' => 'value'], $this->read($source));
  }

  #[Test, Values(['{"a": "v1", "b": "v2"}', '{"a" : "v1", "b" : "v2"}', '{ "a" : "v1" , "b" : "v2" }'])]
  public function read_key_value_pairs($source) {
    Assert::equals(['a' => 'v1', 'b' => 'v2'], $this->read($source));
  }

  #[Test, Values(['{"": "value"}', '{"" : "value"}', '{ "" : "value" }'])]
  public function empty_key($source) {
    Assert::equals(['' => 'value'], $this->read($source));
  }

  #[Test]
  public function keys_overwrite_each_other() {
    Assert::equals(['key' => 'v2'], $this->read('{"key": "v1", "key": "v2"}'));
  }

  #[Test]
  public function object_ending_with_zero() {
    Assert::equals(['key' => 0], $this->read('{"key": 0}'));
  }

  #[Test, Expect(FormatException::class), Values(['{', '{{', '{{}', '}', '}}'])]
  public function unclosed_object($source) {
    $this->read($source);
  }

  #[Test, Expect(FormatException::class)]
  public function missing_key() {
    $this->read('{:"value"}');
  }

  #[Test, Expect(FormatException::class)]
  public function missing_value() {
    $this->read('{"key":}');
  }

  #[Test, Expect(FormatException::class)]
  public function missing_key_and_value() {
    $this->read('{:}');
  }

  #[Test, Expect(FormatException::class)]
  public function missing_colon() {
    $this->read('{"key"}');
  }

  #[Test, Expect(FormatException::class)]
  public function missing_comma_between_key_value_pairs() {
    $this->read('{"a": "v1" "b": "v2"}');
  }

  #[Test, Expect(FormatException::class)]
  public function trailing_comma_in_object() {
    $this->read('{"key": "value",}');
  }

  #[Test, Expect(FormatException::class)]
  public function unquoted_key_in_object() {
    $this->read('{key: "value"}');
  }

  #[Test, Expect(FormatException::class), Values(['{"v1" , "v2"}', '{"v1" => "v2"}', '{"v1" -> "v2"}'])]
  public function incorrect_pair_delimiter($input) {
    $this->read($input);
  }

  #[Test, Expect(FormatException::class), Values(['{"v1":"v2" & "a":"b"}', '{"v1":"v2" : "a":"b"}', '{"v1":"v2" ; "a":"b"}'])]
  public function incorrect_member_delimiter($input) {
    $this->read($input);
  }

  #[Test, Expect(FormatException::class), Values(['{1: "value"}', '{1.0: "value"}', '{true: "value"}', '{false: "value"}', '{null: "value"}', '{[]: "value"}', '{["a"]: "value"}', '{{}: "value"}', '{{"a": "b"}: "value"}'])]
  public function illegal_key($source) {
    $this->read($source);
  }

  #[Test, Values(['[]', '[ ]'])]
  public function read_empty_array($source) {
    Assert::equals([], $this->read($source));
  }

  #[Test, Values(['["value"]', '[ "value" ]'])]
  public function read_list_with_value($source) {
    Assert::equals(['value'], $this->read($source));
  }

  #[Test, Values(['["v1","v2"]', '["v1", "v2"]', '[ "v1", "v2" ]'])]
  public function read_list_with_values($source) {
    Assert::equals(['v1', 'v2'], $this->read($source));
  }

  #[Test, Values(['["v1",["v2","v3"]]', '["v1", ["v2", "v3"]]', '[ "v1" , [ "v2" , "v3" ] ]'])]
  public function read_list_with_nested_list($source) {
    Assert::equals(['v1', ['v2', 'v3']], $this->read($source));
  }

  #[Test]
  public function list_ending_with_zero() {
    Assert::equals([1, 0], $this->read('[1, 0]'));
  }

  #[Test, Expect(FormatException::class), Values(['[', '[[', '[[]', ']', ']]'])]
  public function unclosed_array($source) {
    $this->read($source);
  }

  #[Test, Expect(FormatException::class)]
  public function missing_comma_after_value() {
    $this->read('["v1" "v2"]');
  }

  #[Test, Expect(FormatException::class), Values(['["v1" : "v2"]', '["v1" => "v2"]', '["v1" -> "v2"]'])]
  public function incorrect_array_delimiter($input) {
    $this->read($input);
  }

  #[Test, Expect(FormatException::class)]
  public function trailing_comma_in_array() {
    $this->read('["value",]');
  }

  #[Test, Expect(FormatException::class), Values(['', ' ', '  '])]
  public function empty_input($source) {
    $this->read($source);
  }

  #[Test, Expect(FormatException::class)]
  public function xml_input() {
    $this->read('<xml version="1.0"?><document/>');
  }

  #[Test, Expect(FormatException::class), Values(['UNRECOGNIZED_CONSTANT', "'json does not allow single quoted strings'", "`json does not allow strings in backquores`", '<>', '0.00.1', '0-10', '"a" "b"', '"a", "b"', '{error error}', ' {error error}', '{}}}', '[0-9]{5}'])]
  public function illegal_token($source) {
    $this->read($source);
  }

  #[Test, Values([" [1] ", "  [1]", "\r[1]", "\r\n[1]", "\n[1]", "\n\n[1]", "\t[1]", "\t \t [1]"])]
  public function leading_whitespace_is_ok($source) {
    Assert::equals([1], $this->read($source));
  }

  #[Test, Values(["[1] ", "[1]  ", "[1]\r", "[1]\r\n", "[1]\n", "[1]\n\n", "[1]\t", "[1]\t \t "])]
  public function trailing_whitespace_is_ok($source) {
    Assert::equals([1], $this->read($source));
  }

  #[Test]
  public function files_typically_end_with_trailing_newline() {
    Assert::equals('file-contents', $this->read("\"file-contents\"\n"));
  }

  #[Test]
  public function indented_json() {
    Assert::equals(
      [
        'color' => 'green',
        'sizes' => ['S', 'M', 'L', 'XL'],
        'price' => 12.99
      ],
      $this->read('{
        "color" : "green",
        "sizes" : [ "S", "M", "L", "XL" ],
        "price" : 12.99
      }')
    );
  }

  #[Test, Values(['[1, 2, 3]', '[1,2,3]', '[ 1, 2, 3 ]'])]
  public function can_read_array_sequentially($source) {
    Assert::equals([1, 2, 3], iterator_to_array($this->input($source)->elements()));
  }

  #[Test]
  public function can_read_empty_array_sequentially() {
    foreach ($this->input('[ ]')->elements() as $element) {
      $this->fail('Should not be reached', null, $element);
    }
  }

  #[Test, Expect(class: FormatException::class, message: '/expecting "\["/'), Values(['null', 'false', 'true', '""', '"Test"', '0', '0.0', '{}'])]
  public function cannot_read_other_values_than_arrays_sequentially($source) {
    foreach ($this->input($source)->elements() as $element) {
      $this->fail('Should raise before first element is returned', null, 'lang.FormatException');
    }
  }

  #[Test, Expect(class: FormatException::class, message: '/expecting "," or "\]"/')]
  public function reading_malformed_array_sequentially() {
    foreach ($this->input('[1 2]')->elements() as $element) {
    }
  }

  #[Test, Values(['{"a":"v1","b":"v2"}', '{"a": "v1", "b": "v2"}'])]
  public function can_read_map_sequentially($source) {
    Assert::equals(['a' => 'v1', 'b' => 'v2'], iterator_to_array($this->input($source)->pairs()));
  }

  #[Test]
  public function can_read_empty_map_sequentially() {
    foreach ($this->input('{ }')->pairs() as $key => $value) {
      $this->fail('Should not be reached', null, new Pair($key, $value));
    }
  }

  #[Test, Expect(class: FormatException::class, message: '/expecting "\{"/'), Values(['null', 'false', 'true', '""', '"Test"', '0', '0.0', '[]'])]
  public function cannot_read_other_values_than_pairs_sequentially($source) {
    foreach ($this->input($source)->pairs() as $element) {
      $this->fail('Should raise before first element is returned', null, 'lang.FormatException');
    }
  }

  #[Test, Expect(class: FormatException::class, message: '/expecting ":"/')]
  public function reading_malformed_pairs_sequentially() {
    foreach ($this->input('{"key" "value"}')->pairs() as $element) {
    }
  }

  #[Test]
  public function read_long_text() {
    $str= str_repeat('*', 0xFFFF);
    Assert::equals($str, $this->read('"'.$str.'"'));
  }

  #[Test]
  public function read_long_texts() {
    $str= str_repeat('*', 0xFFFF);
    Assert::equals([$str, $str], $this->read('["'.$str.'", "'.$str.'"]'));
  }

  #[Test, Values([[8191, '"', '\\"'], [8192, '"', '\\"'], [8193, '"', '\\"'], [8188, 'ä', '\\u00e4'], [8189, 'ö', '\\u00f6'], [8193, 'ü', '\\u00fc'], [8182, '💩', '\ud83d\udca9'], [8189, '💩', '\ud83d\udca9'], [8193, '💩', '\ud83d\udca9']])]
  public function read_long_text_with_escape_at_end_of_chunk($length, $escaped, $source) {
    $str= str_repeat('*', $length);
    Assert::equals($str.$escaped, $this->read('"'.$str.$source.'"'));
  }

  #[Test]
  public function read_long_text_with_ws_at_end_of_chunk() {
    $str= str_repeat('*', 8193);
    Assert::equals($str.' ', $this->read('"'.$str.' "'));
  }

  #[Test]
  public function read_whitespace_longer_than_chunk_size() {
    $ws= str_repeat(' ', 8193);
    Assert::equals(['Test', 2], $this->read('["Test",'.$ws.'2]'));
  }

  #[Test, Values(['""', '"Test"'])]
  public function detect_string_type($source) {
    Assert::equals(Types::$STRING, $this->input($source)->type());
  }

  #[Test, Values(['[]', '[1, 2, 3]'])]
  public function detect_array_type($source) {
    Assert::equals(Types::$ARRAY, $this->input($source)->type());
  }

  #[Test, Values(['{}', '{"key": "value"}'])]
  public function detect_object_type($source) {
    Assert::equals(Types::$OBJECT, $this->input($source)->type());
  }

  #[Test, Values(['1', '-1', '0'])]
  public function detect_int_type($source) {
    Assert::equals(Types::$INT, $this->input($source)->type());
  }

  #[Test, Values(['1.0', '-1.0', '0.0', '1e10'])]
  public function detect_double_type($source) {
    Assert::equals(Types::$DOUBLE, $this->input($source)->type());
  }

  #[Test, Values(eval: '[[Types::$NULL, "null"], [Types::$FALSE, "false"], [Types::$TRUE, "true"]]')]
  public function detect_constant_type($type, $source) {
    Assert::equals($type, $this->input($source)->type());
  }

  #[Test]
  public function type_for_empty_input() {
    Assert::null($this->input('')->type());
  }

  #[Test]
  public function type_for_invalid_input() {
    Assert::null($this->input('@invalid@')->type());
  }

  #[Test]
  public function reading_after_detecting_type() {
    $input= $this->input('"Test"');
    $input->type();
    Assert::equals('Test', $input->read());
  }

  #[Test]
  public function detecting_type_after_reading() {
    $input= $this->input('"Test"');
    $input->read();
    Assert::equals(Types::$STRING, $input->type());
  }

  #[Test]
  public function elements_after_detecting_type() {
    $input= $this->input('[1]');
    $input->type();
    Assert::equals([1], iterator_to_array($input->elements()));
  }

  #[Test]
  public function detecting_type_after_elements() {
    $input= $this->input('[1]');
    iterator_to_array($input->elements());
    Assert::equals(Types::$ARRAY, $input->type());
  }

  #[Test]
  public function pairs_after_detecting_type() {
    $input= $this->input('{"key" : "value"}');
    $input->type();
    Assert::equals(['key' => 'value'], iterator_to_array($input->pairs()));
  }

  #[Test]
  public function detecting_type_after_pairs() {
    $input= $this->input('{"key" : "value"}');
    iterator_to_array($input->pairs());
    Assert::equals(Types::$OBJECT, $input->type());
  }

  #[Test]
  public function calling_read_after_resetting() {
    $input= $this->input('[1]');
    Assert::equals([1], $input->read(), '#1');
    $input->reset();
    Assert::equals([1], $input->read(), '#2');
  }

  #[Test]
  public function calling_elements_after_resetting() {
    $input= $this->input('[1]');
    Assert::equals([1], iterator_to_array($input->elements()), '#1');
    $input->reset();
    Assert::equals([1], iterator_to_array($input->elements()), '#2');
  }

  #[Test]
  public function calling_pairs_after_resetting() {
    $input= $this->input('{"key" : "value"}');
    Assert::equals(['key' => 'value'], iterator_to_array($input->pairs()), '#1');
    $input->reset();
    Assert::equals(['key' => 'value'], iterator_to_array($input->pairs()), '#2');
  }
}