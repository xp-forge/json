<?php namespace text\json;

use io\streams\InputStream;

/**
 * Reads JSON from a given input stream
 *
 * ```php
 * $json= new JsonStreamReader((new File('input.json'))->getInputStream()));
 * $value= $json->read();
 * ```
 */
class JsonStreamReader extends JsonReader {

  /**
   * Creates a new stream reader to read from a stream
   *
   * @param  io.streams.InputStream $in
   * @param  string $encoding
   */
  public function __construct(InputStream $in, $encoding= \xp::ENCODING) {
    parent::__construct(new JsonTokenizer($in), $encoding);
  }
}