<?php namespace text\json\unittest;

use text\json\Format;
use unittest\Test;

abstract class FormatTest extends \unittest\TestCase {

  /**
   * Returns a `Format` instance
   *
   * @param  int $options
   * @return text.json.Format
   */
  protected abstract function format($options= 0);

  #[Test]
  public function string() {
    $this->assertEquals('"Test"', $this->format()->representationOf('Test'));
  }

  #[Test]
  public function slash_is_escaped_per_default() {
    $this->assertEquals('"xp-framework\/core"', $this->format()->representationOf('xp-framework/core'));
  }

  #[Test]
  public function unescaped_slash() {
    $this->assertEquals('"xp-framework/core"', $this->format(~Format::ESCAPE_SLASHES)->representationOf('xp-framework/core'));
  }

  #[Test]
  public function unicode_is_escaped_per_default() {
    $this->assertEquals('"\u00dcbercoder"', $this->format()->representationOf('Übercoder'));
  }

  #[Test]
  public function unescaped_unicode() {
    $this->assertEquals('"Übercoder"', $this->format(~Format::ESCAPE_UNICODE)->representationOf('Übercoder'));
  }

  #[Test]
  public function entities_are_not_escaped_per_default() {
    $this->assertEquals('"<a href=\"#top\">&Top<\/a>"', $this->format()->representationOf('<a href="#top">&Top</a>'));
  }

  #[Test]
  public function escaped_entities() {
    $this->assertEquals('"\u003Ca href=\u0022#top\u0022\u003E\u0026Top\u003C\/a\u003E"', $this->format(Format::ESCAPE_ENTITIES)->representationOf('<a href="#top">&Top</a>'));
  }

  #[Test]
  public function int() {
    $this->assertEquals('0', $this->format()->representationOf(0));
  }

  #[Test]
  public function double() {
    $this->assertEquals('0.0', $this->format()->representationOf(0.0));
  }

  #[Test]
  public function true() {
    $this->assertEquals('true', $this->format()->representationOf(true));
  }

  #[Test]
  public function false() {
    $this->assertEquals('false', $this->format()->representationOf(false));
  }

  #[Test]
  public function null() {
    $this->assertEquals('null', $this->format()->representationOf(null));
  }

  #[Test]
  public function empty_array() {
    $this->assertEquals('[]', $this->format()->representationOf([]));
  }

  #[Test]
  public abstract function array_with_one_element();

  #[Test]
  public abstract function array_with_multiple_elements();

  #[Test]
  public abstract function object_with_one_pair();

  #[Test]
  public abstract function object_with_multiple_pairs();

}