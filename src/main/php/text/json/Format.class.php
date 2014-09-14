<?php namespace text\json;

use lang\IllegalArgumentException;

/**
 * JSON format
 *
 * @test  xp://text.json.unittest.DefaultFormatTest
 */
abstract class Format extends \lang\Object {
  public static $DEFAULT, $DENSE;

  static function __static() {
    self::$DEFAULT= new DefaultFormat();
    self::$DENSE= new DenseFormat();
  }

  /**
   * Formats an array
   *
   * @param  var[] $value
   * @return string
   */
  protected abstract function formatArray($value);

  /**
   * Formats an object
   *
   * @param  [:var] $value
   * @return string
   */
  protected abstract function formatObject($value);

  /**
   * Creates a representation of a given value
   *
   * @param  string $value
   * @return string
   */
  public function representationOf($value) {
    $t= gettype($value);
    if ('string' === $t) {
      return json_encode($value);
    } else if ('integer' === $t) {
      return (string)$value;
    } else if ('double' === $t) {
      $string= (string)$value;
      return strpos($string, '.') ? $string : $string.'.0';
    } else if ('array' === $t) {
      if (empty($value)) {
        return '[]';
      } else if (0 === key($value)) {
        return $this->formatArray($value);
      } else {
        return $this->formatObject($value);
      }
    } else if (null === $value) {
      return 'null';
    } else if (true === $value) {
      return 'true';
    } else if (false === $value) {
      return 'false';
    } else {
      throw new IllegalArgumentException('Cannot represent instances of '.typeof($value));
    }
  }
}