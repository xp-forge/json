<?php namespace text\json\unittest;

use text\json\{DenseFormat, Format, WrappedFormat};
use test\Assert;
use test\Test;

class FormatFactoryTest {

  #[Test]
  public function dense() {
    Assert::instance('text.json.DenseFormat', Format::dense());
  }

  #[Test]
  public function dense_without_options() {
    Assert::equals('"http://example.com/"', Format::dense()->representationOf('http://example.com/'));
  }

  #[Test]
  public function dense_with_options() {
    Assert::equals('"http:\/\/example.com\/"', Format::dense(Format::ESCAPE_SLASHES)->representationOf('http://example.com/'));
  }

  #[Test]
  public function wrapped() {
    Assert::instance('text.json.WrappedFormat', Format::wrapped());
  }

  #[Test]
  public function wrapped_without_indent() {
    Assert::equals("{\n    \"key\": \"value\"\n}", Format::wrapped()->representationOf(['key' => 'value']));
  }

  #[Test]
  public function wrapped_with_indent() {
    Assert::equals("{\n  \"key\": \"value\"\n}", Format::wrapped('  ')->representationOf(['key' => 'value']));
  }

  #[Test]
  public function wrapped_without_options() {
    Assert::equals('"http://example.com/"', Format::wrapped('  ')->representationOf('http://example.com/'));
  }

  #[Test]
  public function wrapped_with_options() {
    Assert::equals('"http:\/\/example.com\/"', Format::wrapped('  ', Format::ESCAPE_SLASHES)->representationOf('http://example.com/'));
  }
}