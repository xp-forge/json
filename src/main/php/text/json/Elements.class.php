<?php namespace text\json;

use lang\FormatException;

/**
 * Reads elements from a JSON representation sequentially, that is, parses
 * an element from the underlying input, then returns it immediately for
 * further processing instead of parsing the entire representation first.
 */
class Elements extends \lang\Object implements \Iterator {
  protected $input;
  protected $id= 0;
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
    if (null === $this->id) {
      $this->input->reset();
      $this->id= 0;
    }

    $token= $this->input->firstToken();
    if ('[' === $token) {
      $token= $this->input->nextToken();
      if (']' === $token) {
        $this->id= $this->end();
      } else {
        $this->current= $this->input->valueOf($token);
      }
    } else {
      $this->id= $this->end();
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading elements, expecting "["');
    }
  }

  /** @return var */
  public function current() { return $this->current; }

  /** @return var */
  public function key() { return $this->id; }

  /** @return bool */
  public function valid() { return null !== $this->id; }

  /** @return void */
  public function next() {
    $token= $this->input->nextToken();
    if (',' === $token) {
      $this->current= $this->input->valueOf($this->input->nextToken());
      $this->id++;
    } else if (']' === $token) {
      $this->id= $this->end();
    } else {
      $this->id= $this->end();
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading elements, expecting "," or "]"');
    }
  }

  /** @return var */
  private function end() {
    $this->input->close();
    return null;
  }
}