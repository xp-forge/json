<?php namespace text\json\unittest;

use text\json\JsonReader;
use io\streams\MemoryInputStream;

/**
 * Test JSON reader
 *
 * @see   https://bugs.php.net/bug.php?id=41504
 * @see   https://bugs.php.net/bug.php?id=45791
 * @see   https://bugs.php.net/bug.php?id=54484
 * @see   https://github.com/xp-framework/xp-framework/issues/189
 */
class JsonReaderTest extends \unittest\TestCase {

  /**
   * Helper
   *
   * @param  string $source
   * @return var
   */
  protected function read($source) {
    return (new JsonReader())->read(new MemoryInputStream($source));
  }

  #[@test]
  public function can_create() {
    new JsonReader();
  }

  #[@test, @values([
  #  ['', '""'],
  #  ['Test', '"Test"'],
  #  ['Test the "west"', '"Test the \"west\""'],
  #  ['€uro', '"\u20acuro"'], ['€uro', '"\u20ACuro"'],
  #  ['Knüper', '"Knüper"'], ['Knüper', '"Kn\u00fcper"'],
  #  ["Test\b", '"Test\b"'],
  #  ["Test\f", '"Test\f"'],
  #  ["Test\n", '"Test\n"'],
  #  ["Test\r", '"Test\r"'],
  #  ["Test\t", '"Test\t"'],
  #  ["Test\\", '"Test\\\\"'],
  #  ["Test/", '"Test\/"']
  #])]
  public function read_string($expected, $source) {
    $this->assertEquals($expected, $this->read($source));
  }

  #[@test, @expect('lang.FormatException')]
  public function illegal_escape_sequence() {
    $this->read('"\X"');
  }

  #[@test, @expect('lang.FormatException')]
  public function illegal_encoding() {
    $this->read("\"\xfc\"");
  }

  #[@test]
  public function read_iso_8859_1() {
    $this->assertEquals('ü', (new JsonReader('iso-8859-1'))->read(new MemoryInputStream("\"\xfc\"")));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '"', '"abc', '"abc\"'
  #])]
  public function unclosed_string($source) {
    $this->read($source);
  }

  #[@test, @values([
  #  [0, '0'],
  #  [1, '1'],
  #  [-1, '-1']
  #])]
  public function read_integer($expected, $source) {
    $this->assertEquals($expected, $this->read($source));
  }

  #[@test]
  public function read_int_max() {
    $n= PHP_INT_MAX;
    $this->assertEquals($n, $this->read((string)$n));
  }

  #[@test]
  public function read_int_min() {
    $n= -PHP_INT_MAX -1;
    $this->assertEquals($n, $this->read((string)$n));
  }

  #[@test, @values([
  #  [0.0, '0.0'],
  #  [1.0, '1.0'],
  #  [0.5, '0.5'],
  #  [-1.0, '-1.0'],
  #  [-0.5, '-0.5'],
  #  [0.0000000001, '0.0000000001'],
  #  [9999999999999999999999999999999999999.0, '9999999999999999999999999999999999999'],
  #  [-9999999999999999999999999999999999999.0, '-9999999999999999999999999999999999999']
  #])]
  public function read_double($expected, $source) {
    $this->assertEquals($expected, $this->read($source));
  }

  #[@test, @values([
  #  [10.0, '1E1'], [10.0, '1E+1'], [10.0, '1e1'], [10.0, '1e+1'],
  #  [-10.0, '-1E1'], [-10.0, '-1E+1'], [-10.0, '-1e1'], [-10.0, '-1e+1'],
  #  [0.1, '1E-1'], [0.1, '1e-1'],
  #  [-0.1, '-1E-1'], [0.1, '1e-1'],
  #  [0.0, '0E0'], [0.0, '0e0'],
  #  [1000000.0, '1E6'], [1000000.0, '1e6'],
  #  [-1000000.0, '-1E6'], [-1000000.0, '-1e6']
  #])]
  public function read_exponent($expected, $source) {
    $this->assertEquals($expected, $this->read($source));
  }

  #[@test, @values([
  #  [true, 'true'],
  #  [false, 'false'],
  #  [null, 'null']
  #])]
  public function read_keyword($expected, $source) {
    $this->assertEquals($expected, $this->read($source));
  }

  #[@test, @values(['{}', '{ }'])]
  public function read_empty_object($source) {
    $this->assertEquals([], $this->read($source));
  }

  #[@test, @values([
  #  '{"key": "value"}',
  #  '{"key" : "value"}',
  #  '{ "key" : "value" }'
  #])]
  public function read_key_value_pair($source) {
    $this->assertEquals(['key' => 'value'], $this->read($source));
  }

  #[@test, @values([
  #  '{"a": "v1", "b": "v2"}',
  #  '{"a" : "v1", "b" : "v2"}',
  #  '{ "a" : "v1" , "b" : "v2" }'
  #])]
  public function read_key_value_pairs($source) {
    $this->assertEquals(['a' => 'v1', 'b' => 'v2'], $this->read($source));
  }

  #[@test, @values([
  #  '{"": "value"}',
  #  '{"" : "value"}',
  #  '{ "" : "value" }'
  #])]
  public function empty_key($source) {
    $this->assertEquals(['' => 'value'], $this->read($source));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '{', '{{', '{{}',
  #  '}', '}}'
  #])]
  public function unclosed_object($source) {
    $this->read($source);
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_key() {
    $this->read('{:"value"}');
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_value() {
    $this->read('{"key":}');
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_key_and_value() {
    $this->read('{:}');
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_colon() {
    $this->read('{"key"}');
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_comma_between_key_value_pairs() {
    $this->read('{"a": "v1" "b": "v2"}');
  }

  #[@test, @expect('lang.FormatException')]
  public function trailing_comma_in_object() {
    $this->read('{"key": "value",}');
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '{1: "value"}',
  #  '{1.0: "value"}',
  #  '{true: "value"}', '{false: "value"}', '{null: "value"}',
  #  '{[]: "value"}', '{["a"]: "value"}',
  #  '{{}: "value"}', '{{"a": "b"}: "value"}'
  #])]
  public function illegal_key($source) {
    $this->read($source);
  }

  #[@test, @values(['[]', '[ ]'])]
  public function read_empty_array($source) {
    $this->assertEquals([], $this->read($source));
  }

  #[@test, @values([
  #  '["value"]',
  #  '[ "value" ]'
  #])]
  public function read_list_with_value($source) {
    $this->assertEquals(['value'], $this->read($source));
  }

  #[@test, @values([
  #  '["v1","v2"]',
  #  '["v1", "v2"]',
  #  '[ "v1", "v2" ]'
  #])]
  public function read_list_with_values($source) {
    $this->assertEquals(['v1', 'v2'], $this->read($source));
  }

  #[@test, @values([
  #  '["v1",["v2","v3"]]',
  #  '["v1", ["v2", "v3"]]',
  #  '[ "v1" , [ "v2" , "v3" ] ]'
  #])]
  public function read_list_with_nested_list($source) {
    $this->assertEquals(['v1', ['v2', 'v3']], $this->read($source));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '[', '[[', '[[]',
  #  ']', ']]'
  #])]
  public function unclosed_array($source) {
    $this->read($source);
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_comma_after_value() {
    $this->read('["v1" "v2"]');
  }

  #[@test, @expect('lang.FormatException')]
  public function trailing_comma_in_array() {
    $this->read('["value",]');
  }

  #[@test, @expect('lang.FormatException'), @values(['', ' ', '  '])]
  public function empty_input($source) {
    $this->read($source);
  }

  #[@test, @expect('lang.FormatException')]
  public function xml_input() {
    $this->read('<xml version="1.0"?><document/>');
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  'UNRECOGNIZED_CONSTANT',
  #  "'json does not allow single quoted strings'",
  #  '<>',
  #  '0.00.1',
  #  '0-10',
  #  '"a" "b"',
  #  '"a", "b"'
  #])]
  public function illegal_token($source) {
    $this->read($source);
  }

  #[@test, @values([
  #  " [1] ", "  [1]",
  #  "\r[1]", "\r\n[1]",
  #  "\n[1]", "\n\n[1]",
  #  "\t[1]", "\t \t [1]"
  #])]
  public function leading_whitespace_is_ok($source) {
    $this->assertEquals([1], $this->read($source));
  }

  #[@test, @values([
  #  "[1] ", "[1]  ",
  #  "[1]\r", "[1]\r\n",
  #  "[1]\n", "[1]\n\n",
  #  "[1]\t", "[1]\t \t "
  #])]
  public function trailing_whitespace_is_ok($source) {
    $this->assertEquals([1], $this->read($source));
  }

  #[@test]
  public function indented_json() {
    $this->assertEquals(
      [
        'color' => 'green',
        'sizes' => ['S', 'M', 'L', 'XL'],
        'price' => 12.99
      ],
      (new JsonReader())->read(new MemoryInputStream('{
        "color" : "green",
        "sizes" : [ "S", "M", "L", "XL" ],
        "price" : 12.99
      }'))
    );
  }
}