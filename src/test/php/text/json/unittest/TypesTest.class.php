<?php namespace text\json\unittest;

use text\json\Types;

/**
 * Test JSON types enumeration
 */
class TypesTest extends \unittest\TestCase {

  #[@test]
  public function array_is_array() {
    $this->assertTrue(Types::$ARRAY->isArray());
  }

  #[@test]
  public function object_is_object() {
    $this->assertTrue(Types::$OBJECT->isobject());
  }

  #[@test, @values([
  #  Types::$STRING,
  #  Types::$DOUBLE,
  #  Types::$INT,
  #  Types::$NULL,
  #  Types::$FALSE,
  #  Types::$TRUE,
  #  Types::$OBJECT
  #])]
  public function other_types_are_not_an_array($type) {
    $this->assertFalse($type->isArray());
  }

  #[@test, @values([
  #  Types::$STRING,
  #  Types::$DOUBLE,
  #  Types::$INT,
  #  Types::$NULL,
  #  Types::$FALSE,
  #  Types::$TRUE,
  #  Types::$ARRAY
  #])]
  public function other_types_are_not_an_object($type) {
    $this->assertFalse($type->isObject());
  }
}