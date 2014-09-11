<?php namespace text\json;

use text\StringTokenizer;
use lang\FormatException;

/**
 * Reads JSON from a given string
 *
 * ```php
 * $json= new JsonStringReader('{"Hello": "World"}');
 * $value= $json->read();
 * ```
 */
class JsonStringReader extends JsonReader {

  /**
   * Creates a new stream reader to read from a stream
   *
   * @param  sring $in
   * @param  string $encoding
   */
  public function __construct($in, $encoding= \xp::ENCODING) {
    parent::__construct(new StringTokenizer($in, '{[,"]}:'.self::WHITESPACE, true), $encoding);
  }
}