<?php namespace text\json;

use io\File;
use io\streams\{InputStream, OutputStream};
use lang\IllegalArgumentException;

/**
 * Simple entry point class
 *
 * @see   https://github.com/xp-forge/json/issues/3
 * @test  text.json.unittest.JsonTest
 */
abstract class Json {

  /**
   * Reads from an input
   *
   * @param  string|text.json.Input|io.File|io.stream.InputStream $input
   * @return var
   */
  public static function read($input) {
    if ($input instanceof Input) {
      // NOOP
    } else if ($input instanceof File) {
      $input= new FileInput($input);
    } else if ($input instanceof InputStream) {
      $input= new StreamInput($input);
    } else {
      $input= new StringInput((string)$input);
    }

    return $input->read();
  }

  /**
   * Writes to an output and returns the output
   *
   * @param  var $value
   * @param  text.json.Output|io.File|io.stream.OutputStream $output
   * @return text.json.Output The given output
   * @throws lang.IllegalArgumentException
   */
  public static function write($value, $output) {
    if ($output instanceof Output) {
      // NOOP
    } else if ($output instanceof File) {
      $output= new FileOutput($output);
    } else if ($output instanceof OutputStream) {
      $output= new StreamOutput($output);
    } else {
      throw new IllegalArgumentException('Expected an Output, File or OutputStream, have '.typeof($output));
    }

    $output->write($value);
    return $output;
  }

  /**
   * Returns the output as a string
   *
   * @param  var $value
   * @param  ?text.json.Format $format
   * @return string
   */
  public static function of($value, $format= null) {
    $output= new StringOutput($format ?: Format::$DEFAULT);
    $output->write($value);
    return $output->bytes();
  }
}