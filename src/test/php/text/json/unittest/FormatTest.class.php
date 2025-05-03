<?php namespace text\json\unittest;

use test\{Assert, Before, Test, Values};
use text\json\Format;

abstract class FormatTest {
  protected $format;

  /** @return iterable */
  protected function singleTokens() {
    yield [true, ['true']];
    yield [false, ['false']];
    yield [null, ['null']];
    yield [6100, ['6100']];
    yield [0.5, ['0.5']];
    yield ['Test', ['"Test"']];
    yield [[], ['[]']];
  }

  /**
   * Returns a `Format` instance
   *
   * @param  int $options
   * @return text.json.Format
   */
  protected abstract function format($options= 0);

  #[Test]
  public function string() {
    Assert::equals('"Test"', $this->format()->representationOf('Test'));
  }

  #[Test]
  public function hash_code() {
    Assert::notEquals('', $this->format()->hashCode());
  }

  #[Test]
  public function slash_is_escaped_per_default() {
    Assert::equals('"xp-framework\/core"', $this->format()->representationOf('xp-framework/core'));
  }

  #[Test]
  public function unescaped_slash() {
    Assert::equals('"xp-framework/core"', $this->format(~Format::ESCAPE_SLASHES)->representationOf('xp-framework/core'));
  }

  #[Test]
  public function unicode_is_escaped_per_default() {
    Assert::equals('"\u00dcbercoder"', $this->format()->representationOf('Übercoder'));
  }

  #[Test]
  public function unescaped_unicode() {
    Assert::equals('"Übercoder"', $this->format(~Format::ESCAPE_UNICODE)->representationOf('Übercoder'));
  }

  #[Test]
  public function entities_are_not_escaped_per_default() {
    Assert::equals('"<a href=\"#top\">&Top<\/a>"', $this->format()->representationOf('<a href="#top">&Top</a>'));
  }

  #[Test]
  public function escaped_entities() {
    Assert::equals('"\u003Ca href=\u0022#top\u0022\u003E\u0026Top\u003C\/a\u003E"', $this->format(Format::ESCAPE_ENTITIES)->representationOf('<a href="#top">&Top</a>'));
  }

  #[Test]
  public function int() {
    Assert::equals('0', $this->format()->representationOf(0));
  }

  #[Test]
  public function double() {
    Assert::equals('0.0', $this->format()->representationOf(0.0));
  }

  #[Test]
  public function true() {
    Assert::equals('true', $this->format()->representationOf(true));
  }

  #[Test]
  public function false() {
    Assert::equals('false', $this->format()->representationOf(false));
  }

  #[Test]
  public function null() {
    Assert::equals('null', $this->format()->representationOf(null));
  }

  #[Test]
  public function empty_array() {
    Assert::equals('[]', $this->format()->representationOf([]));
  }

  #[Test]
  public abstract function array_with_one_element();

  #[Test]
  public abstract function array_with_multiple_elements();

  #[Test]
  public abstract function object_with_one_pair();

  #[Test]
  public abstract function object_with_multiple_pairs();

  #[Test, Values(from: 'singleTokens')]
  public function iterate_single_tokens($value, $expected) {
    Assert::equals($expected, iterator_to_array($this->format()->tokensOf($value)));
  }
}