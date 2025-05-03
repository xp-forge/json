<?php namespace text\json;

use ArrayAccess, ArrayIterator, Countable, IteratorAggregate, ReturnTypeWillChange;
use lang\Value;
use util\Objects;

/** @test text.json.JsonObjectTest */
class JsonObject implements ArrayAccess, Countable, IteratorAggregate, Value {
  private $backing;

  /** @param [:var] $backing */
  public function __construct($backing= []) {
    $this->backing= $backing;
  }

  #[ReturnTypeWillChange]
  public function count() { return sizeof($this->backing); }

  #[ReturnTypeWillChange]
  public function offsetGet($key) { return $this->backing[$key]; }

  #[ReturnTypeWillChange]
  public function offsetSet($key, $value) { $this->backing[$key]= $value; }

  #[ReturnTypeWillChange]
  public function offsetExists($key) { return array_key_exists($key, $this->backing); }

  #[ReturnTypeWillChange]
  public function offsetUnset($key) { unset($this->backing[$key]); }

  #[ReturnTypeWillChange]
  public function getIterator() { return new ArrayIterator($this->backing); }

  /** @return string */
  public function toString() {
    return '(object)'.Objects::stringOf($this->backing);
  }

  /** @return string */
  public function hashCode() {
    return 'J'.Objects::hashOf($this->backing);
  }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->backing, $value->backing) : 1;
  }
}