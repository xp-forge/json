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
   * Append a token
   *
   * @param  string $bytes
   * @return void
   */
  public function appendToken($bytes) {
    $this->stream->write($bytes);
  }

  /**
   * Writes a given value
   *
   * @param  var $value
   */
  public function write($value) {
    if (is_array($value)) {
      if (empty($value)) {
        $this->stream->write('[]');
      } else if (0 === key($value)) {
        $this->stream->write('[');
        $next= false;
        foreach ($value as $element) {
          if ($next) {
            $this->stream->write(', ');
          } else {
            $next= true;
          }
          $this->stream->write($this->representationOf($element));
        }
        $this->stream->write(']');
      } else {
        $this->stream->write('{');
        $next= false;
        foreach ($value as $key => $mapped) {
          if ($next) {
            $this->stream->write(', ');
          } else {
            $next= true;
          }
          $this->stream->write($this->representationOf($key).' : '.$this->representationOf($mapped));
        }
        $this->stream->write('}');
      }
    } else {
      $this->stream->write($this->representationOf($value));
    }
    $this->stream->close();
  }

  /** @return io.streams.OutputStream */
  public function stream() { return $this->stream; }
}