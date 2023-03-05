<?php namespace text\json\unittest;

use text\json\Types;
use test\Assert;
use test\{Test, TestCase, Values};

/**
 * Test JSON types enumeration
 */
class TypesTest {

  #[Test]
  public function array_is_array() {
    Assert::true(Types::$ARRAY->isArray());
  }

  #[Test]
  public function object_is_object() {
    Assert::true(Types::$OBJECT->isobject());
  }

  #[Test, Values(eval: '[Types::$STRING, Types::$DOUBLE, Types::$INT, Types::$NULL, Types::$FALSE, Types::$TRUE, Types::$OBJECT]')]
  public function other_types_are_not_an_array($type) {
    Assert::false($type->isArray());
  }

  #[Test, Values(eval: '[Types::$STRING, Types::$DOUBLE, Types::$INT, Types::$NULL, Types::$FALSE, Types::$TRUE, Types::$ARRAY]')]
  public function other_types_are_not_an_object($type) {
    Assert::false($type->isObject());
  }
}