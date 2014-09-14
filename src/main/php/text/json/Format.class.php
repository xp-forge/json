<?php namespace text\json;

use lang\IllegalArgumentException;

/**
 * JSON format
 *
 * @test  xp://text.json.unittest.DefaultFormatTest
 */
class Format extends \lang\Object {
  public static $DEFAULT;

  static function __static() {
    self::$DEFAULT= new self();
  }

  /**
   * Formats an array
   *
   * @param  var[] $value
   * @return string
   */
  protected function formatArray($value) {
    $r= '[';
    $next= false;
    foreach ($value as $element) {
      if ($next) {
        $r.= ', ';
      } else {
        $next= true;
      }
      $r.= $this->representationOf($element);
    }
    return $r.']';
  }

  /**
   * Formats an object
   *
   * @param  [:var] $value
   * @return string
   */
  protected function formatObject($value) {
    $r= '{';
    $next= false;
    foreach ($value as $key => $mapped) {
      if ($next) {
        $r.= ', ';
      } else {
        $next= true;
      }
      $r.= $this->representationOf($key).' : '.$this->representationOf($mapped);
    }
    return $r.'}';
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