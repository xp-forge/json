<?php namespace text\json;

use StdClass, Traversable;
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
    $r= '';
    foreach ($this->tokensOf($value) as $bytes) {
      $r.= $bytes;
    }
    return $r;
  }

  /**
   * Yields tokens for a given value
   *
   * @param  var $value
   * @return iterable
   */
  public function tokensOf($value) {
    if (is_string($value)) {
      yield json_encode($value, $this->options);
    } else if (is_int($value)) {
      yield (string)$value;
    } else if (is_float($value)) {
      $cast= (string)$value;
      yield strpos($cast, '.') ? $cast : $cast.'.0';
    } else if (is_array($value)) {
      if (empty($value)) {
        yield '[]';
      } else if (0 === key($value)) {
        yield $this->open('[');
        $i= 0;
        foreach ($value as $element) {
          if ($i++) yield $this->comma;
          yield from $this->tokensOf($element);
        }
        yield $this->close(']');
      } else {
        map: yield $this->open('{');
        $i= 0;
        foreach ($value as $key => $element) {
          if ($i++) yield $this->comma;
          yield from $this->tokensOf((string)$key);
          yield $this->colon;
          yield from $this->tokensOf($element);
        }
        yield $this->close('}');
      }
    } else if (null === $value) {
      yield 'null';
    } else if (true === $value) {
      yield 'true';
    } else if (false === $value) {
      yield 'false';
    } else if ($value instanceof JsonObject || $value instanceof StdClass) {
      goto map;
    } else if ($value instanceof Traversable) {
      $i= 0;
      $map= null;
      foreach ($value as $key => $element) {
        if (0 === $i++) {
          $map= 0 !== $key;
          yield $this->open($map ? '{' : '[');
        } else {
          yield $this->comma;
        }

        if ($map) {
          yield from $this->tokensOf((string)$key);
          yield $this->colon;
        }
        yield from $this->tokensOf($element);
      }
      yield null === $map ? '[]' : $this->close($map ? '}' : ']');
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