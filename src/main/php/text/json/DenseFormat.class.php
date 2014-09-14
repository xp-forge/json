<?php namespace text\json;

/**
 * Dense JSON format - no insignificant whitespace between tokens. Ideal
 * use on network I/O.
 *
 * @test  xp://text.json.unittest.DenseFormatTest
 */
class DenseFormat extends Format {

  static function __static() { }

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
        $r.= ',';
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
        $r.= ',';
      } else {
        $next= true;
      }
      $r.= $this->representationOf($key).':'.$this->representationOf($mapped);
    }
    return $r.'}';
  }
}