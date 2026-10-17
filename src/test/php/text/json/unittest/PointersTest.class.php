<?php namespace text\json\unittest;

use lang\FormatException;
use test\{Assert, Expect, Test, Values};
use text\json\{Pointers, Types, StringInput};

class PointersTest {

  /** @return iterable */
  private function values() {
    yield ['"test"', 'test'];
    yield ['true', true];
    yield ['false', false];
    yield ['null', null];
    yield ['1', 1];
    yield ['1.5', 1.5];
  }

  #[Test]
  public function can_create() {
    new Pointers(new StringInput(''));
  }

  #[Test, Values(from: 'values')]
  public function toplevel($input, $expected) {
    Assert::equals(['' => $expected], iterator_to_array(new Pointers(new StringInput($input))));
  }

  #[Test]
  public function empty_array() {
    Assert::equals(['' => Types::$ARRAY], iterator_to_array(new Pointers(new StringInput('[]'))));
  }

  #[Test]
  public function empty_object() {
    Assert::equals(['' => Types::$OBJECT], iterator_to_array(new Pointers(new StringInput('{}'))));
  }

  #[Test]
  public function rfc_example() {
    $input= <<<'JSON'
      {
        "foo": ["bar", "baz"],
        "": 0,
        "a/b": 1,
        "c%d": 2,
        "e^f": 3,
        "g|h": 4,
        "i\\j": 5,
        "k\"l": 6,
        " ": 7,
        "m~n": 8
      }
      JSON
    ;
    Assert::equals(
      [
        ''       => Types::$OBJECT,
        '/foo'   => Types::$ARRAY,
        '/foo/0' => 'bar',
        '/foo/1' => 'baz',
        '/'      => 0,
        '/a~1b'  => 1,
        '/c%d'   => 2,
        '/e^f'   => 3,
        '/g|h'   => 4,
        '/i\\j'  => 5,
        '/k"l'   => 6,
        '/ '     => 7,
        '/m~0n'  => 8,
      ],
      iterator_to_array(new Pointers(new StringInput($input)))
    );
  }

  #[Test]
  public function composer_file() {
    $input= <<<'JSON'
      {
        "name": "example/test",
        "keywords": ["module", "xp"],
        "require": {
          "xp-forge/json": "^6.1",
          "php": ">=7.4.0"
        },
        "autoload" : {
          "files" : ["src/main/php/autoload.php"]
        }
      }
      JSON
    ;
    Assert::equals(
      [
        ''                        => Types::$OBJECT,
        '/name'                   => 'example/test',
        '/keywords'               => Types::$ARRAY,
        '/keywords/0'             => 'module',
        '/keywords/1'             => 'xp',
        '/require'                => Types::$OBJECT,
        '/require/xp-forge~1json' => '^6.1',
        '/require/php'            => '>=7.4.0',
        '/autoload'               => Types::$OBJECT,
        '/autoload/files'         => Types::$ARRAY,
        '/autoload/files/0'       => 'src/main/php/autoload.php',
      ],
      iterator_to_array(new Pointers(new StringInput($input)))
    );
  }

  #[Test, Expect(class: FormatException::class, message: 'Empty input')]
  public function empty_input() {
    iterator_to_array(new Pointers(new StringInput('')));
  }

  #[Test, Expect(class: FormatException::class, message: 'Unexpected token ["test"] reading value')]
  public function invalid_literal() {
    iterator_to_array(new Pointers(new StringInput('test')));
  }

  #[Test, Expect(class: FormatException::class, message: 'Unexpected token ["2"] reading array, expecting "," or "]"')]
  public function missing_comma_in_array() {
    iterator_to_array(new Pointers(new StringInput('[1 2]')));
  }

  #[Test, Expect(class: FormatException::class, message: 'Unexpected token ["2"] reading object, expecting ":"')]
  public function missing_colon_in_object() {
    iterator_to_array(new Pointers(new StringInput('{"key" 2}')));
  }

  #[Test, Expect(class: FormatException::class, message: 'Unexpected token ["2"] reading object, expecting "," or "}"')]
  public function missing_comma_in_object() {
    iterator_to_array(new Pointers(new StringInput('{"key": "value" 2}')));
  }
}