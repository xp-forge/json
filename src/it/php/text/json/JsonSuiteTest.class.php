<?php namespace text\json;

use io\Folder;
use lang\FormatException;
use test\{Args, Assert, Expect, Test, Values};
use text\json\FileInput;

#[Args('folder')]
class JsonSuiteTest {
  private $base;
  private static $IGNORED= [
    'n_string_UTF8_surrogate_U+D800.json',
    'n_string_unescaped_tab.json',
    'n_string_unescaped_newline.json',
    'n_string_unescaped_ctrl_char.json',
    'string_1_escaped_invalid_codepoint.json',
    'string_1_invalid_codepoint.json',
    'string_2_escaped_invalid_codepoints.json',
    'string_2_invalid_codepoints.json',
    'string_3_escaped_invalid_codepoints.json',
    'string_3_invalid_codepoints.json'
  ];

  /**
   * Creates JSON test suite from a folder with `test_parsing` and
   * `test_transform` subfolders inside. These contain files with the
   * following naming convention:
   *
   * - `y_`: content must be accepted by parsers
   * - `n_`: content must be rejected by parsers
   * - `i_`: parsers are free to accept or reject content
   *
   * @param  string $folder
   */
  public function __construct($folder= '.') {
    $this->base= new Folder($folder);
    ini_set('xdebug.max_nesting_level', -1);
  }

  /** @return iterable */
  public function files($folder) {
    foreach ((new Folder($this->base, $folder))->entries() as $entry) {
      in_array($entry->name(), self::$IGNORED) || yield $entry;
    }
  }

  #[Test, SelectFiles(from: 'files', filter: 'test_parsing/y_*')]
  public function must_accept_parsing($entry) {
    (new FileInput($entry))->read();
  }

  #[Test, Expect(FormatException::class), SelectFiles(from: 'files', filter: 'test_parsing/n_*')]
  public function must_reject_parsing($entry) {
    (new FileInput($entry))->read();
  }

  #[Test, SelectFiles(from: 'files', filter: 'test_parsing/i_*')]
  public function is_free_to_either_accept_or_reject($entry) {
    try {
      (new FileInput($entry))->read();
    } catch (FormatException $ignored) { }
  }

  #[Test, SelectFiles(from: 'files', filter: 'test_transform/*')]
  public function may_understand_differently($entry) {
    (new FileInput($entry))->read();
  }
}