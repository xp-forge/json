<?php namespace text\json;

use lang\FormatException;

/**
 * Reads elements from a JSON representation sequentially, that is, parses
 * an element from the underlying reader, then returns it immediately for
 * further processing instead of parsing the entire representation first.
 */
class Elements extends \lang\Object implements \Iterator {

  /**
   * Creates a new elements iterator
   *
   * @param  text.json.JsonReader
   */
  public function __construct(JsonReader $reader) {
    $this->reader= $reader;
  }
  
  /** @return void */
  public function rewind() {
    $this->reader->reset();
    $token= $this->reader->nextToken();
    if ('[' === $token) {
      $this->current= $this->reader->nextValue();
      $this->id= 0;
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
    $token= $this->reader->nextToken();
    if (',' === $token) {
      $this->current= $this->reader->nextValue();
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