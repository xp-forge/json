<?php namespace text\json;

/**
 * Wrapped JSON format - indents objects
 *
 * @test  xp://text.json.unittest.PrettyFormatTest
 */
class WrappedFormat extends Format {
  protected $indent;
  protected $level= 1;

  static function __static() { }

  /**
   * Creates a new wrapped format
   *
   * @param  string $indent If omitted, uses 2 spaces
   */
  public function __construct($indent= '  ') {
    $this->indent= $indent;
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
    $indent= str_repeat($this->indent, $this->level);
    $r= "{\n".$indent;
    $next= false;
    foreach ($value as $key => $mapped) {
      if ($next) {
        $r.= ",\n".$indent;
      } else {
        $next= true;
      }
      $r.= $this->representationOf($key).' : ';
      $this->level++;
      $r.= $this->representationOf($mapped);
      $this->level--;
    }
    return $r."\n".str_repeat($this->indent, $this->level - 1)."}";
  }
}