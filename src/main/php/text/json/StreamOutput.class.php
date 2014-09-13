<?php namespace text\json;

use io\streams\OutputStream;

/**
 * Writes JSON to a given output stream
 *
 * ```php
 * $json= new StreamOutput((new stream('output.json'))->getOutputStream()));
 * $json->write('Hello World');
 * ```
 *
 * @test  xp://text.json.unittest.StreamOutputTest
 */
class StreamOutput extends Output {
  protected $stream;

  /**
   * Creates a new instance
   *
   * @param  io.streams.OutputStream $out
   * @param  string $encoding
   */
  public function __construct(OutputStream $out, $encoding= \xp::ENCODING) {
    parent::__construct($encoding);
    $this->stream= $out;
  }

  /**
   * Writes a given value
   *
   * @param  var $value
   */
  public function write($value) {
    parent::write($value);
    $this->stream->close();
  }

  /**
   * Append a token
   *
   * @param  string $bytes
   * @return void
   */
  public function appendToken($bytes) {
    $this->stream->write($bytes);
  }

  /** @return io.streams.OutputStream */
  public function stream() { return $this->stream; }
}