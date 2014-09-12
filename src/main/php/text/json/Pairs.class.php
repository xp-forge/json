<?php namespace text\json;

use lang\FormatException;

/**
 * Reads key/value pairs from a JSON representation sequentially, that is,
 * parses an element from the underlying reader, then returns it immediately 
 * for further processing instead of parsing the entire representation first.
 */
class Pairs extends \lang\Object implements \Iterator {
  protected $reader;
  protected $key= null;
  protected $value= null;

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
    if ('{' === $token) {
      $this->key= $this->reader->nextValue();
      if (':' === ($token= $this->reader->nextToken())) {
        $this->value= $this->reader->nextValue();
      } else {
        $this->key= null;
        throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading pairs, expecting ":"');
      }
    } else {
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading pairs, expecting "{"');
    }
  }

  /** @return var */
  public function current() {
    return $this->value;
  }

  /** @return var */
  public function key() {
    return $this->key;
  }

  /** @return void */
  public function next() {
    $token= $this->reader->nextToken();
    if (',' === $token) {
      $this->key= $this->reader->nextValue();
      if (':' === ($token= $this->reader->nextToken())) {
        $this->value= $this->reader->nextValue();
      } else {
        $this->key= null;
        throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading pairs, expecting ":"');
      }
    } else if ('}' === $token) {
      $this->key= null;
    } else {
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading elements, expecting "," or "]"');
    }
  }

  /** @return bool */
  public function valid() {
    return null !== $this->key;
  }
}