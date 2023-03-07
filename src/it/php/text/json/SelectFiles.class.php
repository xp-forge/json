<?php namespace text\json;

use test\Provider;
use test\execution\Context;

class SelectFiles implements Provider {
  private $from, $folder, $match;

  /**
   * Selects files
   *
   * @param  string $from The instance method to invoke
   * @param  string $filter Glob filter, using `/`
   */
  public function __construct($from, $filter) {
    $this->from= $from;
    sscanf($filter, "%[^/]/%[^\r]", $this->folder, $this->match);
  }

  /** @return iterable */
  public function values(Context $context) {
    foreach ($context->instance->{$this->from}($this->folder) as $entry) {
      fnmatch($this->match, $entry->name()) && yield [$entry];
    }
  }
}