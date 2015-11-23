<?php namespace text\json;

abstract class Json extends \lang\Object {

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
   * @param  text.json.Output|text.json.Format $arg
   * @return text.json.Output The given output, or a StringOutput if a format is given
   */
  public static function write($value, $arg= null) {
    if ($arg instanceof Format) {
      $output= new StringOutput($arg);
    } else if ($arg instanceof Output) {
      $output= $arg;
    } else {
      $output= new StringOutput(Format::$DEFAULT);
    }
    $output->write($value);
    return $output;
  }
}