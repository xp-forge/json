<?php namespace text\json\unittest;

abstract class JsonOutputTest extends \unittest\TestCase {
  private static $precision;

  /** @return void */
  #[@beforeClass]
  public static function usePrecision() {
    self::$precision= ini_set('precision', 14);
  }

  /** @return void */
  #[@afterClass]
  public static function resetPrecision() {
    ini_set('precision', self::$precision);
  }

  /**
   * Helper
   *
   * @param  var $value
   * @param  string $encoding
   * @return string
   */
  protected abstract function write($value, $encoding= 'utf-8');

  #[@test, @values([
  #  ['""', ''],
  #  ['"Test"', 'Test'],
  #  ['"Test \"the\" west"', 'Test "the" west'],
  #  ['"\u20acuro"', '€uro'],
  #  ['"Test the \"west\""', 'Test the "west"'],
  #  ['"Test\b"', "Test\x08"],
  #  ['"Test\f"', "Test\x0c"],
  #  ['"Test\n"', "Test\x0a"],
  #  ['"Test\r"', "Test\x0d"],
  #  ['"Test\t"', "Test\x09"],
  #  ['"Test\\\\"', "Test\\"],
  #  ['"Test\/"', "Test/"]
  #])]
  public function read_string($expected, $value) {
    $this->assertEquals($expected, $this->write($value));
  }

  #[@test, @values([
  #  ['1', 1],
  #  ['0', 0],
  #  ['-1', -1]
  #])]
  public function write_integer($source, $input) {
    $this->assertEquals($source, $this->write($input));
  }

  #[@test]
  public function write_int_max() {
    $n= PHP_INT_MAX;
    $this->assertEquals((string)$n, $this->write($n));
  }

  #[@test]
  public function write_int_min() {
    $n= -PHP_INT_MAX -1;
    $this->assertEquals((string)$n, $this->write($n));
  }

  #[@test, @values([
  #  ['0.0', 0.0],
  #  ['1.0', 1.0],
  #  ['0.5', 0.5],
  #  ['-1.0', -1.0],
  #  ['-0.5', -0.5],
  #  ['1.0E-10', 0.0000000001],
  #  ['1.0E+37', 9999999999999999999999999999999999999.0],
  #  ['-1.0E+37', -9999999999999999999999999999999999999.0]
  #])]
  public function write_double($expected, $value) {
    $this->assertEquals($expected, $this->write($value));
  }

  #[@test, @values([
  #  ['true', true],
  #  ['false', false],
  #  ['null', null]
  #])]
  public function write_keyword($expected, $write) {
    $this->assertEquals($expected, $this->write($write));
  }

  #[@test]
  public function write_empty_array() {
    $this->assertEquals('[]', $this->write([]));
  }

  #[@test, @values([
  #  ['[1]', [1]],
  #  ['[1, 2]', [1, 2]],
  #  ['[[1], [2, 3]]', [[1], [2, 3]]]
  #])]
  public function write_array($expected, $write) {
    $this->assertEquals($expected, $this->write($write));
  }

  #[@test, @values([
  #  ['{"" : "value"}', ['' => 'value']],
  #  ['{"key" : "value"}', ['key' => 'value']],
  #  ['{"a" : "v1", "b" : "v2"}', ['a' => 'v1', 'b' => 'v2']]
  #])]
  public function write_object($expected, $write) {
    $this->assertEquals($expected, $this->write($write));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_write_closures() {
    $this->write(function() { });
  }
}