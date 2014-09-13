<?php namespace text\json;

use lang\FormatException;

/**
 * Reads elements from a JSON representation sequentially, that is, parses
 * an element from the underlying input, then returns it immediately for
 * further processing instead of parsing the entire representation first.
 */
class Elements extends \lang\Object implements \Iterator {
  protected $input;
  protected $id= -1;
  protected $current= null;

  /**
   * Creates a new elements iterator
   *
   * @param  text.json.Input $input
   */
  public function __construct(Input $input) {
    $this->input= $input;
  }
  
  /** @return void */
  public function rewind() {
    $token= $this->input->firstToken();
    if ('[' === $token) {
      $token= $this->input->nextToken();
      if (']' === $token) {
        $this->id= -1;
      } else {
        $this->current= $this->input->nextValue($token);
        $this->id= 0;
      }
    } else {
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading elements, expecting "["');
    }
  }

  /** @return var */
  public function current() {
    return $this->current;
  }

  /** @return var */
  public function key() {
    return $this->id;
  }

  /** @return void */
  public function next() {
    $token= $this->input->nextToken();
    if (',' === $token) {
      $this->current= $this->input->nextValue();
      $this->id++;
    } else if (']' === $token) {
      $this->id= -1;
    } else {
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading elements, expecting "," or "]"');
    }
  }

  /** @return bool */
  public function valid() {
    return -1 !== $this->id;
  }
}