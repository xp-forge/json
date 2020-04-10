<?php namespace text\json\unittest;

use text\json\{DenseFormat, Format, WrappedFormat};

class FormatFactoryTest extends \unittest\TestCase {

  #[@test]
  public function dense() {
    $this->assertInstanceOf('text.json.DenseFormat', Format::dense());
  }

  #[@test]
  public function dense_without_options() {
    $this->assertEquals('"http://example.com/"', Format::dense()->representationOf('http://example.com/'));
  }

  #[@test]
  public function dense_with_options() {
    $this->assertEquals('"http:\/\/example.com\/"', Format::dense(Format::ESCAPE_SLASHES)->representationOf('http://example.com/'));
  }

  #[@test]
  public function wrapped() {
    $this->assertInstanceOf('text.json.WrappedFormat', Format::wrapped());
  }

  #[@test]
  public function wrapped_without_indent() {
    $this->assertEquals("{\n    \"key\": \"value\"\n}", Format::wrapped()->representationOf(['key' => 'value']));
  }

  #[@test]
  public function wrapped_with_indent() {
    $this->assertEquals("{\n  \"key\": \"value\"\n}", Format::wrapped('  ')->representationOf(['key' => 'value']));
  }

  #[@test]
  public function wrapped_without_options() {
    $this->assertEquals('"http://example.com/"', Format::wrapped('  ')->representationOf('http://example.com/'));
  }

  #[@test]
  public function wrapped_with_options() {
    $this->assertEquals('"http:\/\/example.com\/"', Format::wrapped('  ', Format::ESCAPE_SLASHES)->representationOf('http://example.com/'));
  }
}