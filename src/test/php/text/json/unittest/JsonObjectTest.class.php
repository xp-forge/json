<?php namespace text\json\unittest;

use lang\IndexOutOfBoundsException;
use test\{Assert, Test, Values};
use text\json\JsonObject;

class JsonObjectTest {

  #[Test]
  public function can_create() {
    new JsonObject();
  }

  #[Test, Values([[[]], [['key' => 'value']]])]
  public function can_create_with($backing) {
    new JsonObject($backing);
  }

  #[Test]
  public function get() {
    Assert::equals('value', (new JsonObject(['key' => 'value']))['key']);
  }

  #[Test]
  public function null_coalesce() {
    Assert::equals('default', (new JsonObject())['key'] ?? 'default');
  }

  #[Test]
  public function set() {
    $fixture= new JsonObject(['key' => 'value']);
    $fixture['key']= 'changed';

    Assert::equals('changed', $fixture['key']);
  }

  #[Test]
  public function isset() {
    $fixture= new JsonObject(['key' => 'value', 'price' => null]);

    Assert::true(isset($fixture['key']));
    Assert::false(isset($fixture['price']));
    Assert::false(isset($fixture['color']));
  }

  #[Test]
  public function unset() {
    $fixture= new JsonObject(['key' => 'value']);
    unset($fixture['key']);

    Assert::throws(IndexOutOfBoundsException::class, function() use($fixture) {
      $fixture['key'];
    });
  }

  #[Test, Values([[[]], [['key' => 'value']], [['a' => 0, 'b' => 1]]])]
  public function iteration($backing) {
    Assert::equals($backing, iterator_to_array(new JsonObject($backing)));
  }

  #[Test, Values([[[], 0], [['key' => 'value'], 1], [['a' => 0, 'b' => 1], 2]])]
  public function count($backing, $expected) {
    Assert::equals($expected, sizeof(new JsonObject($backing)));
  }

  #[Test]
  public function compare() {
    $a= new JsonObject(['key' => 'value']);
    $b= new JsonObject(['key' => 'value']);
    $c= new JsonObject(['key' => 'VALUE']);

    Assert::equals(0, $a->compareTo($b));
    Assert::equals(1, $a->compareTo($c));
    Assert::equals(-1, $c->compareTo($a));
  }

  #[Test, Values([[[], '(object)[]'], [['key' => 'value'], '(object)[key => "value"]']])]
  public function string_representation($backing, $expected) {
    Assert::equals($expected, (new JsonObject($backing))->toString());
  }
}