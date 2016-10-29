<?php namespace text\json;

/**
 * Simple entry point class
 *
 * @see   https://github.com/xp-forge/json/issues/3
 * @test  xp://text.json.unittest.JsonTest
 */
abstract class Json {

  /**
   * Reads from an input
   *
   * @param  string|text.json.Input $input
   * @return var
   */
  public static function read($input) {
    if ($input instanceof Input) {
      return $input->read();
    } else {
      return (new StringInput($input))->read();
    }
  }

  /**
   * Writes to an output and returns the output
   *
   * @param  var $value
   * @param  text.json.Output $output
   * @return text.json.Output The given output
   */
  public static function write($value, Output $output) {
    $output->write($value);
    return $output;
  }

  /**
   * Returns the output as a string
   *
   * @param  var $value
   * @param  text.json.Format $format
   * @return string
   */
  public static function of($value, Format $format= null) {
    $output= new StringOutput($format ?: Format::$DEFAULT);
    $output->write($value);
    return $output->bytes();
  }
}