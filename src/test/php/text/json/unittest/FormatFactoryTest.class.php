<?php namespace text\json\unittest;

use text\json\{DenseFormat, Format, WrappedFormat};
use unittest\Test;

class FormatFactoryTest extends \unittest\TestCase {

  #[Test]
  public function dense() {
    $this->assertInstanceOf('text.json.DenseFormat', Format::dense());
  }

  #[Test]
  public function dense_without_options() {
    $this->assertEquals('"http://example.com/"', Format::dense()->representationOf('http://example.com/'));
  }

  #[Test]
  public function dense_with_options() {
    $this->assertEquals('"http:\/\/example.com\/"', Format::dense(Format::ESCAPE_SLASHES)->representationOf('http://example.com/'));
  }

  #[Test]
  public function wrapped() {
    $this->assertInstanceOf('text.json.WrappedFormat', Format::wrapped());
  }

  #[Test]
  public function wrapped_without_indent() {
    $this->assertEquals("{\n    \"key\": \"value\"\n}", Format::wrapped()->representationOf(['key' => 'value']));
  }

  #[Test]
  public function wrapped_with_indent() {
    $this->assertEquals("{\n  \"key\": \"value\"\n}", Format::wrapped('  ')->representationOf(['key' => 'value']));
  }

  #[Test]
  public function wrapped_without_options() {
    $this->assertEquals('"http://example.com/"', Format::wrapped('  ')->representationOf('http://example.com/'));
  }

  #[Test]
  public function wrapped_with_options() {
    $this->assertEquals('"http:\/\/example.com\/"', Format::wrapped('  ', Format::ESCAPE_SLASHES)->representationOf('http://example.com/'));
  }
}