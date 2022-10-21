<?php namespace text\json;

use StdClass;
use lang\{IllegalArgumentException, Value};

/**
 * JSON format
 *
 * @test  text.json.unittest.FormatFactoryTest
 */
abstract class Format implements Value {
  const ESCAPE_SLASHES = -65;  // ~JSON_UNESCAPED_SLASHES
  const ESCAPE_UNICODE = -257; // ~JSON_UNESCAPED_UNICODE
  const ESCAPE_ENTITIES = 11;  // JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT

  public static $DEFAULT;
  public $comma, $colon, $options;

  static function __static() {
    self::$DEFAULT= new DenseFormat();
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
   * Creates a new dense format
   *
   * @param  int $options
   * @return self
   */
  public static function dense($options= 0) {
    return new DenseFormat($options ?: ~self::ESCAPE_SLASHES);
  }

  /**
   * Creates a new wrapped format
   *
   * @param  string $indent
   * @param  int $options
   * @return self
   */
  public static function wrapped($indent= '    ', $options= 0) {
    return new WrappedFormat($indent, $options ?: ~self::ESCAPE_SLASHES);
  }

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
   * @param  var $value
   * @return string
   */
  public function representationOf($value) {
    $t= gettype($value);
    if ('string' === $t) {
      return json_encode($value, $this->options);
    } else if ('integer' === $t) {
      return (string)$value;
    } else if ('double' === $t) {
      $cast= (string)$value;
      return strpos($cast, '.') ? $cast : $cast.'.0';
    } else if ('array' === $t) {
      if (empty($value)) {
        return '[]';
      } else if (0 === key($value)) {
        $r= $this->open('[');
        $next= false;
        foreach ($value as $element) {
          if ($next) {
            $r.= $this->comma;
          } else {
            $next= true;
          }
          $r.= $this->representationOf($element);
        }
        return $r.$this->close(']');
      } else { map:
        $r= $this->open('{');
        $next= false;
        foreach ($value as $key => $mapped) {
          if ($next) {
            $r.= $this->comma;
          } else {
            $next= true;
          }
          $r.= $this->representationOf($key).$this->colon.$this->representationOf($mapped);
        }
        return $r.$this->close('}');
      }
    } else if (null === $value) {
      return 'null';
    } else if (true === $value) {
      return 'true';
    } else if (false === $value) {
      return 'false';
    } else if ($value instanceof StdClass) {
      $value= (array)$value;
      if (empty($value)) return '{}';
      goto map;
    } else {
      throw new IllegalArgumentException('Cannot represent instances of '.typeof($value));
    }
  }

  /** @return string */
  public function toString() { return nameof($this); }

  /** @return string */
  public function hashCode() { return spl_object_hash($this); }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value === $this ? 0 : 1;
  }
}