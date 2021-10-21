<?php namespace text\json;

use Iterator, ReturnTypeWillChange;
use lang\FormatException;
use util\Objects;

/**
 * Reads key/value pairs from a JSON representation sequentially, that is,
 * parses an element from the underlying input, then returns it immediately 
 * for further processing instead of parsing the entire representation first.
 */
class Pairs implements Iterator {
  protected $input;
  protected $key= null;
  protected $value= null;

  /**
   * Creates a new elements iterator
   *
   * @param  text.json.Input $input
   */
  public function __construct(Input $input) {
    $this->input= $input;
  }

  /**
   * Reads next value
   *
   * @return var
   * @throws lang.FormatException
   */
  private function nextValue() {
    if (':' === ($token= $this->input->nextToken())) {
      return $this->input->valueOf($this->input->nextToken());
    } else {
      $this->end();
      throw new FormatException('Unexpected token ['.Objects::stringOf($token).'] reading pairs, expecting ":"');
    }
  }
  
  /** @return void */
  #[ReturnTypeWillChange]
  public function rewind() {
    $token= $this->input->firstToken();
    if ('{' === $token) {
      $token= $this->input->nextToken();
      if ('}' === $token) {
        $this->key= $this->end();
      } else {
        $this->key= $this->input->valueOf($token);
        $this->value= $this->nextValue();
      }
    } else {
      $this->key= $this->end();
      throw new FormatException('Unexpected token ['.Objects::stringOf($token).'] reading pairs, expecting "{"');
    }
  }

  /** @return var */
  #[ReturnTypeWillChange]
  public function current() { return $this->value; }

  /** @return var */
  #[ReturnTypeWillChange]
  public function key() { return $this->key; }

  /** @return bool */
  #[ReturnTypeWillChange]
  public function valid() { return null !== $this->key; }

  /** @return void */
  #[ReturnTypeWillChange]
  public function next() {
    $token= $this->input->nextToken();
    if (',' === $token) {
      $this->key= $this->input->valueOf($this->input->nextToken());
      $this->value= $this->nextValue();
    } else if ('}' === $token) {
      $this->key= $this->end();
    } else {
      $this->key= $this->end();
      throw new FormatException('Unexpected token ['.Objects::stringOf($token).'] reading elements, expecting "," or "}"');
    }
  }

  /** @return var */
  private function end() {
    $this->input->close();
    return null;
  }
}