<?php namespace text\json;

use lang\IllegalArgumentException;

abstract class Output extends \lang\Object {
  public $format;

  /**
   * Creates a new instance
   *
   * @param  text.json.Format $format
   */
  public function __construct(Format $format= null) {
    $this->format= $format ?: Format::$DEFAULT;
  }

  /**
   * Writes a given value
   *
   * @param  var $value
   * @return self
   */
  public function write($value) {
    $f= $this->format;
    if (is_array($value)) {
      if (empty($value)) {
        $this->appendToken('[]');
      } else if (0 === key($value)) {
        $next= false;
        foreach ($value as $element) {
          if ($next) {
            $t= $f->comma;
          } else {
            $t= $f->open('[');
            $next= true;
          }
          $this->appendToken($t.$f->representationOf($element));
        }
        $this->appendToken($f->close(']'));
      } else {
        $next= false;
        foreach ($value as $key => $mapped) {
          if ($next) {
            $t= $f->comma;
          } else {
            $t= $f->open('{');
            $next= true;
          }
          $this->appendToken($t.$f->representationOf($key).$f->colon.$f->representationOf($mapped));
        }
        $this->appendToken($f->close('}'));
      }
    } else {
      $this->appendToken($f->representationOf($value));
    }
    return $this;
  }

  /**
   * Append a token
   *
   * @param  string $bytes
   * @return void
   */
  public abstract function appendToken($bytes);

  /**
   * Begin a sequential output stream
   *
   * @param  text.json.Types $t either Types::$OBJECT or Types::$ARRAY
   * @return text.json.Stream
   * @throws lang.IllegalArgumentException
   */
  public function begin(Types $t) {
    if ($t->isArray()) {
      return new ArrayStream($this);
    } else if ($t->isObject()) {
      return new ObjectStream($this);
    } else {
      throw new IllegalArgumentException('Expecting either an array or an object, '.$t->name().' given');
    }
  }

  /** @return void */
  public function close() {
    // Does nothing
  }

  /**
   * The destructor takes care of closing this output
   */
  public function __destruct() {
    $this->close();
  }
}