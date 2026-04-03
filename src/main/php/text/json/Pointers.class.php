<?php namespace text\json;

use IteratorAggregate, Traversable;
use lang\FormatException;
use util\Objects;

/**
 * JSON pointers enumeration
 *
 * @test  text.json.unittest.PointersTest
 * @see   https://datatracker.ietf.org/doc/html/rfc6901
 */
class Pointers implements IteratorAggregate {
  private $input;

  /** @param text.json.Input $input */
  public function __construct(Input $input) { $this->input= $input; }

  /**
   * Yields pointers
   *
   * @param  string $base
   * @param  var $token
   * @return iterable
   */
  private function pointers($base, $token) {
    static $escape= ['/' => '~1', '~' => '~0'];

    if (true === $token[0]) {
      yield $base => $token[1];
    } else if ('{' === $token) {
      yield $base => Types::$OBJECT;

      do {
        $key= $this->input->nextToken();
        if ('}' === $key) break;

        $token= $this->input->nextToken();
        if (':' === $token) {
          yield from $this->pointers($base.'/'.strtr($key[1], $escape), $this->input->nextToken());
        } else {
          throw new FormatException('Unexpected token ['.Objects::stringOf($token).'] reading object, expecting ":"');
        }

        $token= $this->input->nextToken();
        if (',' === $token) {
          continue;
        } else if ('}' === $token) {
          break;
        } else {
          throw new FormatException('Unexpected token ['.Objects::stringOf($token).'] reading object, expecting "," or "}"');
        }
      } while (true);
    } else if ('[' === $token) {
      yield $base => Types::$ARRAY;

      $i= 0;
      do {
        $value= $this->input->nextToken();
        if (']' === $value) break;

        yield from $this->pointers($base.'/'.($i++), $value);

        $token= $this->input->nextToken();
        if (',' === $token) {
          continue;
        } else if (']' === $token) {
          break;
        } else {
          throw new FormatException('Unexpected token ['.Objects::stringOf($token).'] reading array, expecting "," or "]"');
        }
      } while (true);
    } else if ('true' === $token) {
      yield $base => true;
    } else if ('false' === $token) {
      yield $base => false;
    } else if ('null' === $token) {
      yield $base => null;
    } else if (is_numeric($token)) {
      yield $base => $token > PHP_INT_MAX || $token < -PHP_INT_MAX- 1 || strcspn($token, '.eE') < strlen($token)
        ? (float)$token
        : (int)$token
      ;
    } else {
      throw new FormatException('Unexpected token ['.Objects::stringOf($token).'] reading value');
    }
  }

  /** Start yielding from first input token */
  public function getIterator(): Traversable {
    if (null === ($token= $this->input->firstToken())) {
      throw new FormatException('Empty input');
    }

    yield from $this->pointers('', $token);
  }
}