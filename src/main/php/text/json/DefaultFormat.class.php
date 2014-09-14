<?php namespace text\json;

/**
 * Default JSON format.
 *
 * @test  xp://text.json.unittest.DefaultFormatTest
 */
class DefaultFormat extends Format {

  static function __static() { }

  /**
   * Creates a new dense format
   */
  public function __construct() {
    parent::__construct(', ', ' : ');
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
}