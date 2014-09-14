<?php namespace text\json;

use lang\IllegalArgumentException;

/**
 * JSON format
 *
 * @test  xp://text.json.unittest.DefaultFormatTest
 */
abstract class Format extends \lang\Object {
  const ESCAPE_SLASHES = -65;  // ~JSON_UNESCAPED_SLASHES
  const ESCAPE_UNICODE = -257; // ~JSON_UNESCAPED_UNICODE
  const ESCAPE_ENTITIES = 11;  // JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT

  public static $DEFAULT, $NATURAL;
  public $comma;
  public $colon;

  static function __static() {
    self::$DEFAULT= new DenseFormat();
    self::$NATURAL= new WrappedFormat('    ', ~self::ESCAPE_SLASHES);
  }

  /**
   * Creates a new wrapped format
   *
   * @param  string $comma
   * @param  string $comma
   * @param  int $options
   */
  public function __construct($comma, $colon, $options= 0) {
    $this->comma= $comma;
    $this->colon= $colon;
    $this->options= $options;
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
   * Open an array or object
   *
   * @param  string $token either `[` or `{`
   * @param  string
   */
  public function open($token) {
    return $token;
  }

  /**
   * Close an array or object
   *
   * @param  string $token either `]` or `}`
   * @param  string
   */
  public function close($token) {
    return $token;
  }

  /**
   * Creates a representation of a given value
   *
   * @param  string $value
   * @return string
   */
  public function representationOf($value) {
    $t= gettype($value);
    if ('string' === $t) {
      return json_encode($value, $this->options);
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