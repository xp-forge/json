<?php namespace text\json;

use lang\IllegalArgumentException;

abstract class Output extends \lang\Object {
  protected $encoding;

  /**
   * Creates a new instance
   *
   * @param  string $encoding
   */
  public function __construct($encoding= \xp::ENCODING) {
    $this->encoding= $encoding;
  }

  /**
   * Creates a representation of a given value
   *
   * @param  string $value
   * @return string
   */
  public function representationOf($value) {
    $t= gettype($value);
    if ('string' === $t) {
      return json_encode($value);
    } else if ('integer' === $t) {
      return (string)$value;
    } else if ('double' === $t) {
      $string= (string)$value;
      return strpos($string, '.') ? $string : $string.'.0';
    } else if ('array' === $t) {
      if (empty($value)) {
        return '[]';
      } else if (0 === key($value)) {
        $inner= '[';
        $next= false;
        foreach ($value as $element) {
          if ($next) {
            $inner.= ', ';
          } else {
            $next= true;
          }
          $inner.= $this->representationOf($element);
        }
        return $inner.']';
      } else {
        $inner= '{';
        $next= false;
        foreach ($value as $key => $mapped) {
          if ($next) {
            $inner.= ', ';
          } else {
            $next= true;
          }
          $inner.= $this->representationOf($key).' : '.$this->representationOf($mapped);
        }
        return $inner.'}';
      }
    } else if (null === $value) {
      return 'null';
    } else if (true === $value) {
      return 'true';
    } else if (false === $value) {
      return 'false';
    } else {
      throw new IllegalArgumentException('Cannot represent instances of '.typeof($value));
    }
  }

  /**
   * Writes a given value
   *
   * @param  var $value
   */
  public function write($value) {
    if (is_array($value)) {
      if (empty($value)) {
        $this->appendToken('[]');
      } else if (0 === key($value)) {
        $next= false;
        foreach ($value as $element) {
          if ($next) {
            $t= ', ';
          } else {
            $t= '[';
            $next= true;
          }
          $this->appendToken($t.$this->representationOf($element));
        }
        $this->appendToken(']');
      } else {
        $next= false;
        foreach ($value as $key => $mapped) {
          if ($next) {
            $t= ', ';
          } else {
            $t= '{';
            $next= true;
          }
          $this->appendToken($t.$this->representationOf($key).' : '.$this->representationOf($mapped));
        }
        $this->appendToken('}');
      }
    } else {
      $this->appendToken($this->representationOf($value));
    }
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