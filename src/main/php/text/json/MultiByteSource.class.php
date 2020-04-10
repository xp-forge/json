<?php namespace text\json;

use io\IOException;
use io\streams\{InputStream, Seekable, Streams};

class MultiByteSource implements InputStream, Seekable {
  protected $in= null;

  /**
   * Constructor. Creates a new MultiByteSource on an underlying input
   * stream with a given charset.
   *
   * @param   io.streams.InputStream $encoding
   * @param   string $encoding
   */
  public function __construct(InputStream $stream, $encoding) {
    $this->in= $stream;
    $this->encoding= $encoding;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   * @return  int
   */
  public function available() {
    return $this->in->available();
  }

  /**
   * Seek
   *
   * @param  int $offset
   * @param  int $whence
   * @return void
   */
  public function seek($offset, $whence= SEEK_SET) {
    if ($this->in instanceof Seekable) {
      $this->in->seek($offset, $whence);
    } else {
      throw new IOException('Cannot seek '.$this->in->toString());
    }
  }

  /**
   * Tell
   *
   * @return int
   */
  public function tell() {
    return $this->in->tell();
  }

  /**
   * Read a number of characters
   *
   * @param   int size default 8192
   * @return  string NULL when end of data is reached
   */
  public function read($size= 8192) {
    if (0 === $size) return '';

    $chunk= '';
    $count= $size - 4;
    do {
      $chunk.= $this->in->read($count);
      $count= 1;
    } while (false === ($result= @iconv($this->encoding, \xp::ENCODING, $chunk)) && $this->in->available());
    
    return $result;
  }

  /**
   * Close this multi-byte source
   *
   * @return void
   */
  public function close() {
    $this->in->close();
  }
}