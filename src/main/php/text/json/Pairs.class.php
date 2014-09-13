<?php namespace text\json;

use lang\FormatException;

/**
 * Reads key/value pairs from a JSON representation sequentially, that is,
 * parses an element from the underlying input, then returns it immediately 
 * for further processing instead of parsing the entire representation first.
 */
class Pairs extends \lang\Object implements \Iterator {
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
  
  /** @return void */
  public function rewind() {
    $token= $this->input->firstToken();
    if ('{' === $token) {
      $token= $this->input->nextToken();
      if ('}' === $token) {
        $this->key= null;
      } else {
        $this->key= $this->input->valueOf($token);
        if (':' === ($token= $this->input->nextToken())) {
          $this->value= $this->input->valueOf($this->input->nextToken());
        } else {
          $this->key= null;
          throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading pairs, expecting ":"');
        }
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
    $token= $this->input->nextToken();
    if (',' === $token) {
      $this->key= $this->input->valueOf($this->input->nextToken());
      if (':' === ($token= $this->input->nextToken())) {
        $this->value= $this->input->valueOf($this->input->nextToken());
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