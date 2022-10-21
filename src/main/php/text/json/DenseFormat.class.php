<?php namespace text\json;

/**
 * Dense JSON format - no insignificant whitespace between tokens. Ideal
 * use on network I/O.
 *
 * @test  xp://text.json.unittest.DenseFormatTest
 */
class DenseFormat extends Format {

  static function __static() { }

  /**
   * Creates a new dense format
   *
   * @param  int $options
   */
  public function __construct($options= 0) {
    parent::__construct(',', ':', $options);
  }
}