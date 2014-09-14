<?php namespace text\json;

use lang\IllegalArgumentException;

/**
 * JSON format
 */
class Format extends \lang\Object {
  public static $DEFAULT;

  static function __static() {
    self::$DEFAULT= new self();
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
        $inner= '[';
        $next= false;
        foreach ($value as $element) {
          if ($next) {
            $inner.= ', ';
          } else {
            $next= true;
          }
          $inner.= $this->representationOf($element);
        }
        return $inner.']';
      } else {
        $inner= '{';
        $next= false;
        foreach ($value as $key => $mapped) {
          if ($next) {
            $inner.= ', ';
          } else {
            $next= true;
          }
          $inner.= $this->representationOf($key).' : '.$this->representationOf($mapped);
        }
        return $inner.'}';
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