<?php namespace text\json\unittest;

use io\streams\MemoryInputStream;
use util\collections\Pair;

/**
 * Test JSON reader
 *
 * @see   https://bugs.php.net/bug.php?id=41504
 * @see   https://bugs.php.net/bug.php?id=45791
 * @see   https://bugs.php.net/bug.php?id=54484
 * @see   https://github.com/xp-framework/xp-framework/issues/189
 */
abstract class JsonReaderTest extends \unittest\TestCase {

  /**
   * Returns the reader implementation
   *
   * @param  string $source
   * @param  string $encoding
   * @return text.json.JsonReader
   */
  protected abstract function reader($source, $encoding= 'utf-8');

  /**
   * Helper
   *
   * @param  string $source
   * @param  string $encoding
   * @return var
   */
  protected function read($source, $encoding= 'utf-8') {
    return $this->reader($source, $encoding)->read();
  }

  #[@test, @values([
  #  ['', '""'],
  #  ['Test', '"Test"'],
  #  ['Test the "west"', '"Test the \"west\""'],
  #  ['Test "the" west', '"Test \"the\" west"'],
  #  ['€uro', '"\u20acuro"'], ['€uro', '"\u20ACuro"'],
  #  ['Knüper', '"Knüper"'], ['Knüper', '"Kn\u00fcper"'],
  #  ["Test\x08", '"Test\b"'],
  #  ["Test\x0c", '"Test\f"'],
  #  ["Test\x0a", '"Test\n"'],
  #  ["Test\x0d", '"Test\r"'],
  #  ["Test\x09", '"Test\t"'],
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
    $this->assertEquals('ü', $this->read("\"\xfc\"", 'iso-8859-1'));
  }

  #[@test]
  public function read_iso_8859_15() {
    $this->assertEquals('ü€', $this->read("\"\xfc\u20ac\"", 'iso-8859-15'));
  }

  #[@test, @expect(class= 'lang.FormatException', withMessage= 'Unclosed string'), @values([
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
  public function files_typically_end_with_trailing_newline() {
    $this->assertEquals('file-contents', $this->read("\"file-contents\"\n"));
  }

  #[@test]
  public function indented_json() {
    $this->assertEquals(
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

  #[@test, @values(['[1, 2, 3]', '[1,2,3]', '[ 1, 2, 3 ]'])]
  public function can_read_array_sequentially($source) {
    $r= [];
    foreach ($this->reader($source)->elements() as $element) {
      $r[]= $element;
    }
    $this->assertEquals([1, 2, 3], $r);
  }

  #[@test]
  public function can_read_empty_array_sequentially() {
    foreach ($this->reader('[ ]')->elements() as $element) {
      $this->fail('Should not be reached', null, $element);
    }
  }

  #[@test, @expect(class= 'lang.FormatException', withMessage= '/expecting "\["/'), @values([
  #  'null', 'false', 'true',
  #  '""', '"Test"',
  #  '0', '0.0',
  #  '{}'
  #])]
  public function cannot_read_other_values_than_arrays_sequentially($source) {
    foreach ($this->reader($source)->elements() as $element) {
      $this->fail('Should raise before first element is returned', null, 'lang.FormatException');
    }
  }

  #[@test, @expect(class= 'lang.FormatException', withMessage= '/expecting "," or "\]"/')]
  public function reading_malformed_array_sequentially() {
    foreach ($this->reader('[1 2]')->elements() as $element) {
    }
  }

  #[@test, @values(['{"a":"v1","b":"v2"}', '{"a": "v1", "b": "v2"}'])]
  public function can_read_map_sequentially($source) {
    $r= [];
    foreach ($this->reader($source)->pairs() as $key => $value) {
      $r[$key]= $value;
    }
    $this->assertEquals(['a' => 'v1', 'b' => 'v2'], $r);
  }

  #[@test]
  public function can_read_empty_map_sequentially() {
    foreach ($this->reader('{ }')->pairs() as $key => $value) {
      $this->fail('Should not be reached', null, new Pair($key, $value));
    }
  }

  #[@test, @expect(class= 'lang.FormatException', withMessage= '/expecting "\{"/'), @values([
  #  'null', 'false', 'true',
  #  '""', '"Test"',
  #  '0', '0.0',
  #  '[]'
  #])]
  public function cannot_read_other_values_than_pairs_sequentially($source) {
    foreach ($this->reader($source)->pairs() as $element) {
      $this->fail('Should raise before first element is returned', null, 'lang.FormatException');
    }
  }

  #[@test, @expect(class= 'lang.FormatException', withMessage= '/expecting ":"/')]
  public function reading_malformed_pairs_sequentially() {
    foreach ($this->reader('{"key" "value"}')->pairs() as $element) {
    }
  }

  #[@test]
  public function read_long_text() {
    $str= str_repeat('*', 0xFFFF);
    $this->assertEquals($str, $this->read('"'.$str.'"'));
  }

  #[@test]
  public function read_long_texts() {
    $str= str_repeat('*', 0xFFFF);
    $this->assertEquals([$str, $str], $this->read('["'.$str.'", "'.$str.'"]'));
  }

  #[@test, @values([
  #  ['"', '\\"'],
  #  ['ü', '\u00fc']
  #])]
  public function read_long_text_with_escape_at_end_of_chunk($escaped, $source) {
    $str= str_repeat('*', 8190);
    $this->assertEquals($str.$escaped, $this->read('"'.$str.$source.'"'));
  }
}