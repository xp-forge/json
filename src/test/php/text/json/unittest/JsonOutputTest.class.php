<?php namespace text\json\unittest;

use lang\IllegalArgumentException;
use test\{Assert, After, Before, Expect, Test, Values};
use text\json\Types;

abstract class JsonOutputTest {
  private static $precision;

  /**
   * Returns the implementation
   *
   * @return text.json.Output
   */
  protected abstract function output();

  /**
   * Returns the result
   *
   * @param  text.json.Output $out
   * @return string
   */
  protected abstract function result($out);

  /**
   * Helper
   *
   * @param  var $value
   * @param  string $encoding
   * @return string
   */
  protected function write($value) {
    return $this->result($this->output()->write($value));
  }

  /** @return iterable */
  private function iterables() {
    yield ['[]', new \ArrayIterator([])];
    yield ['[1]', new \ArrayIterator([1])];
    yield ['[1,2]', new \ArrayIterator([1, 2])];
    yield ['{"key":"value"}', new \ArrayIterator(['key' => 'value'])];
    yield ['{"a":"v1","b":"v2"}', new \ArrayIterator(['a' => 'v1', 'b' => 'v2'])];
    yield ['[1,[2,3]]', new \ArrayIterator([1, new \ArrayIterator([2, 3])])];
    yield ['[1,[2,3]]', [1, new \ArrayIterator([2, 3])]];
    yield ['{"a":"v1","b":{"c":"v2"}}', new \ArrayIterator(['a' => 'v1', 'b' => new \ArrayIterator(['c' => 'v2'])])];
    yield ['{"a":"v1","b":{"c":"v2"}}', ['a' => 'v1', 'b' => new \ArrayIterator(['c' => 'v2'])]];
    yield ['[1,{"key":"value"}]', new \ArrayIterator([1, new \ArrayIterator(['key' => 'value'])])];
    yield ['{"key":[1,2]}', new \ArrayIterator(['key' => new \ArrayIterator([1, 2])])];
  }

  /** @return iterable */
  private function generators() {
    yield ['[1,2]', function() { yield 1; yield 2; }];
    yield ['{"key":"value"}', function() { yield 'key' => 'value'; }];
  }

  #[Before]
  public function usePrecision() {
    self::$precision= ini_set('precision', 14);
  }

  #[After]
  public function resetPrecision() {
    ini_set('precision', self::$precision);
  }

  #[Test, Values([['""', ''], ['"Test"', 'Test'], ['"Test \"the\" west"', 'Test "the" west'], ['"Test the \"west\""', 'Test the "west"'], ['"Test\b"', "Test\x08"], ['"Test\f"', "Test\x0c"], ['"Test\n"', "Test\x0a"], ['"Test\r"', "Test\x0d"], ['"Test\t"', "Test\x09"], ['"Test\\\\"', "Test\\"], ['"Test\/"', "Test/"]])]
  public function write_string($expected, $value) {
    Assert::equals($expected, $this->write($value));
  }

  #[Test, Values([['"\u20acuro"', '€uro'], ['"\u00dcbercoder"', 'Übercoder'], ['"Poop = \ud83d\udca9"', 'Poop = 💩']])]
  public function write_unicode($expected, $value) {
    Assert::equals($expected, $this->write($value));
  }

  #[Test, Values([['1', 1], ['0', 0], ['-1', -1]])]
  public function write_integer($source, $input) {
    Assert::equals($source, $this->write($input));
  }

  #[Test]
  public function write_int_max() {
    $n= PHP_INT_MAX;
    Assert::equals((string)$n, $this->write($n));
  }

  #[Test]
  public function write_int_min() {
    $n= -PHP_INT_MAX -1;
    Assert::equals((string)$n, $this->write($n));
  }

  #[Test, Values([['0.0', 0.0], ['1.0', 1.0], ['0.5', 0.5], ['-1.0', -1.0], ['-0.5', -0.5], ['1.0E-10', 0.0000000001], ['1.0E+37', 9999999999999999999999999999999999999.0], ['-1.0E+37', -9999999999999999999999999999999999999.0]])]
  public function write_double($expected, $value) {
    Assert::equals($expected, $this->write($value));
  }

  #[Test, Values([['true', true], ['false', false], ['null', null]])]
  public function write_keyword($expected, $write) {
    Assert::equals($expected, $this->write($write));
  }

  #[Test]
  public function write_empty_array() {
    Assert::equals('[]', $this->write([]));
  }

  #[Test, Values([['[1]', [1]], ['[1,2]', [1, 2]], ['[[1],[2,3]]', [[1], [2, 3]]]])]
  public function write_array($expected, $write) {
    Assert::equals($expected, $this->write($write));
  }

  #[Test]
  public function write_empty_object() {
    Assert::equals('{}', $this->write((object)[]));
  }

  #[Test]
  public function write_array_as_object() {
    Assert::equals('{0:1,1:2,2:3}', $this->write((object)[1, 2, 3]));
  }

  #[Test]
  public function write_nested_array_as_object() {
    Assert::equals('{"values":{0:1,1:2,2:3}}', $this->write(['values' => (object)[1, 2, 3]]));
  }

  #[Test]
  public function write_map_as_object() {
    Assert::equals('{"key":"value"}', $this->write((object)['key' => 'value']));
  }

  #[Test, Values([['{"":"value"}', ['' => 'value']], ['{"key":"value"}', ['key' => 'value']], ['{"123":456}', [123 => 456]], ['{"a":"v1","b":"v2"}', ['a' => 'v1', 'b' => 'v2']]])]
  public function write_object($expected, $write) {
    Assert::equals($expected, $this->write($write));
  }

  #[Test, Values(from: 'iterables')]
  public function write_iterable($expected, $write) {
    Assert::equals($expected, $this->write($write));
  }

  #[Test, Values(from: 'generators')]
  public function write_generator($expected, $write) {
    Assert::equals($expected, $this->write($write()));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_write_closures() {
    $this->write(function() { });
  }

  #[Test]
  public function begin_an_array() {
    $this->output()->begin(Types::$ARRAY);
  }

  #[Test]
  public function begin_an_object() {
    $this->output()->begin(Types::$OBJECT);
  }

  #[Test, Expect(IllegalArgumentException::class), Values(eval: '[Types::$STRING, Types::$DOUBLE, Types::$INT, Types::$NULL, Types::$FALSE, Types::$TRUE]')]
  public function begin_another_type_raises_an_exception($type) {
    $this->output()->begin($type);
  }

  #[Test]
  public function write_empty_array_sequentially() {
    $out= $this->output();
    $out->begin(Types::$ARRAY)->close();
    Assert::equals('[]', $this->result($out));
  }

  #[Test]
  public function write_array_sequentially() {
    $out= $this->output();
    with ($out->begin(Types::$ARRAY), function($array) {
      $array->element(1);
      $array->element(2);
      $array->element(3);
    });
    Assert::equals('[1,2,3]', $this->result($out));
  }

  #[Test]
  public function write_empty_object_sequentially() {
    $out= $this->output();
    $out->begin(Types::$OBJECT)->close();
    Assert::equals('{}', $this->result($out));
  }

  #[Test]
  public function write_object_sequentially() {
    $out= $this->output();
    with ($out->begin(Types::$OBJECT), function($array) {
      $array->pair('a', 'v1');
      $array->pair('b', 'v2');
    });
    Assert::equals('{"a":"v1","b":"v2"}', $this->result($out));
  }
}