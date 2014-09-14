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
   * @param  text.json.Format $format
   */
  public function __construct(OutputStream $out, Format $format= null) {
    parent::__construct($format);
    $this->stream= $out;
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

  /** @return void */
  public function close() { $this->stream->close(); }

  /** @return io.streams.OutputStream */
  public function stream() { return $this->stream; }
}