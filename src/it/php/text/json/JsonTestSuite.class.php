<?php namespace text\json;

use text\json\FileInput;
use io\Folder;
use lang\FormatException;

class JsonTestSuite extends \unittest\TestCase {
  private $folder;
  private static $IGNORED= [
    'n_structure_100000_opening_arrays.json',
    'n_structure_open_array_object.json',
    'n_number_then_00.json',
    'n_string_UTF8_surrogate_U+D800.json',
    'n_string_unescaped_tab.json',
    'n_string_unescaped_newline.json',
    'n_string_unescaped_crtl_char.json'
  ];

  /**
   * Constructor
   *
   * @param  string $name
   * @param  string $folder Folder with `test_parsing` inside
   */
  public function __construct($name, $folder= '.') {
    parent::__construct($name);
    $this->folder= new Folder($folder);
  }

  /**
   * Returns parsing tests. The name of these files tell if their contents
   * should be accepted or rejected.
   *
   * - `y_`: content must be accepted by parsers
   * - `n_`: content must be rejected by parsers
   * - `i_`: parsers are free to accept or reject content
   *
   * @param  string $filter
   * @return php.Generator
   */
  private function parsing($filter) {
    foreach ((new Folder($this->folder, 'test_parsing'))->entries() as $entry) {
      if (!in_array($entry->name(), self::$IGNORED) && 0 === strncmp($filter, $entry->name(), strlen($filter))) {
        yield [$entry];
      }
    }
  }

  #[@test, @values(source= 'parsing', args= ['y_'])]
  public function must_accept_parsing($entry) {
    (new FileInput($entry))->read();
  }

  #[@test, @expect(FormatException::class), @values(source= 'parsing', args= ['n_'])]
  public function must_reject_parsing($entry) {
    (new FileInput($entry))->read();
  }

  #[@test, @values(source= 'parsing', args= ['i_'])]
  public function is_free_to_either_accept_or_reject($entry) {
    try {
      (new FileInput($entry))->read();
    } catch (FormatException $ignored) { }
  }
}