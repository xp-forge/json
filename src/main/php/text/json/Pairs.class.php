<?php namespace text\json;

use lang\FormatException;

/**
 * Reads key/value pairs from a JSON representation sequentially, that is,
 * parses an element from the underlying input, then returns it immediately 
 * for further processing instead of parsing the entire representation first.
 */
class Pairs extends \lang\Object implements \Iterator {
  protected $input;
  protected $key= true;
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
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading pairs, expecting ":"');
    }
  }
  
  /** @return void */
  public function rewind() {
    if (null === $this->key) {
      $this->input->reset();
    }

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
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading pairs, expecting "{"');
    }
  }

  /** @return var */
  public function current() { return $this->value; }

  /** @return var */
  public function key() { return $this->key; }

  /** @return bool */
  public function valid() { return null !== $this->key; }

  /** @return void */
  public function next() {
    $token= $this->input->nextToken();
    if (',' === $token) {
      $this->key= $this->input->valueOf($this->input->nextToken());
      $this->value= $this->nextValue();
    } else if ('}' === $token) {
      $this->key= $this->end();
    } else {
      $this->key= $this->end();
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading elements, expecting "," or "}"');
    }
  }

  /** @return var */
  private function end() {
    $this->input->close();
    return null;
  }
}