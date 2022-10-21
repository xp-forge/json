<?php namespace text\json;

/**
 * Wrapped JSON format - indents objects and first-level arrays.
 *
 * @test  text.json.unittest.WrappedFormatTest
 */
class WrappedFormat extends Format {
  protected $indent;
  protected $level= 1;
  protected $stack= [];

  static function __static() { }

  /**
   * Creates a new wrapped format
   *
   * @param  int|string $indent If omitted, uses 2 spaces
   * @param  int $options
   */
  public function __construct($indent= '  ', $options= 0) {
    parent::__construct(', ', ': ', $options);
    $this->indent= is_string($indent) ? $indent : str_repeat(' ', $indent);
  }

  /**
   * Formats an array
   *
   * @param  var[] $value
   * @return string
   */
  protected function formatArray($value) {
    $comma= $this->comma;
    $this->comma= ', ';
    $r= '[';
    $next= false;
    foreach ($value as $element) {
      if ($next) {
        $r.= $this->comma;
      } else {
        $next= true;
      }
      $r.= $this->representationOf($element);
    }
    $this->comma= $comma;
    return $r.']';
  }

  /**
   * Formats an object
   *
   * @param  [:var] $value
   * @return string
   */
  protected function formatObject($value) {
    $comma= $this->comma;
    $indent= str_repeat($this->indent, $this->level);
    $this->comma= ",\n".$indent;
    $r= "{\n".$indent;
    $next= false;
    $this->level++;
    foreach ($value as $key => $mapped) {
      if ($next) {
        $r.= $this->comma;
      } else {
        $next= true;
      }
      $r.= $this->representationOf($key).$this->colon.$this->representationOf($mapped);
    }
    $this->level--;
    $indent= str_repeat($this->indent, $this->level - 1);
    $this->comma= $comma;
    return $r."\n".$indent.'}';
  }

  public function open($token) {
    if ('{' === $token || empty($this->stack)) {
      $this->stack[]= $this->comma;
      $indent= str_repeat($this->indent, $this->level++);
      $this->comma= ",\n".$indent;
      return $token."\n".$indent;
    } else {
      $this->stack[]= $this->comma;
      $this->comma= ', ';
      return $token;
    }
  }

  public function close($token) {
    $this->comma= array_pop($this->stack);
    if ('}' === $token || empty($this->stack)) {
      $this->level--;
      return "\n".str_repeat($this->indent, $this->level - 1).$token;
    } else {
      return $token;
    }
  }
}