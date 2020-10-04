<?php namespace text\json\unittest;

use text\json\Types;
use unittest\{Test, TestCase, Values};

/**
 * Test JSON types enumeration
 */
class TypesTest extends TestCase {

  #[Test]
  public function array_is_array() {
    $this->assertTrue(Types::$ARRAY->isArray());
  }

  #[Test]
  public function object_is_object() {
    $this->assertTrue(Types::$OBJECT->isobject());
  }

  #[Test, Values(eval: '[Types::$STRING, Types::$DOUBLE, Types::$INT, Types::$NULL, Types::$FALSE, Types::$TRUE, Types::$OBJECT]')]
  public function other_types_are_not_an_array($type) {
    $this->assertFalse($type->isArray());
  }

  #[Test, Values(eval: '[Types::$STRING, Types::$DOUBLE, Types::$INT, Types::$NULL, Types::$FALSE, Types::$TRUE, Types::$ARRAY]')]
  public function other_types_are_not_an_object($type) {
    $this->assertFalse($type->isObject());
  }
}