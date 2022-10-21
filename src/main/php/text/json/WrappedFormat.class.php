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
   * Open an array or object
   *
   * @param  string $token either `[` or `{`
   * @param  string
   */
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

  /**
   * Close an array or object
   *
   * @param  string $token either `]` or `}`
   * @param  string
   */
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