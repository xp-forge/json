<?php namespace text\json;

use lang\IllegalArgumentException;

abstract class Output extends \lang\Object {
  protected $encoding;

  protected static $escapes= [
    0x08 => '\\b',
    0x09 => '\\t',
    0x0A => '\\n', 
    0x0C => '\\f', 
    0x0D => '\\r', 
    0x22 => '\\"', 
    0x2F => '\\/', 
    0x5C => '\\\\'
  ];

  /**
   * Creates a new instance
   *
   * @param  string $encoding
   */
  public function __construct($encoding= \xp::ENCODING) {
    $this->encoding= $encoding;
  }

  /**
   * Escapes escape sequences inside string
   *
   * @param   string in utf8-encoded string
   * @return  string
   */
  protected function escape($in) {
    $out= '';
    for ($i= 0, $s= strlen($in); $i < $s; $i++) {
      $c= ord($in{$i});
      if (isset(self::$escapes[$c])) {
        $out.= self::$escapes[$c];
      } else if ($c < 0x20) {
        $out.= sprintf('\u%04x', $c);
      } else if ($c < 0x80) {
        $out.= $in{$i};
      } else if ($c < 0xE0) {
        $out.= sprintf('\u%04x', (($c & 0x1F) << 6) | (ord($in{$i+ 1}) & 0x3F));
        $i+= 1;
      } else if ($c < 0xF0) {
        $out.= sprintf('\u%04x', (($c & 0x0F) << 12) | ((ord($in{$i+ 1}) & 0x3F) << 6) | (ord($in{$i+ 2}) & 0x3F));
        $i+= 2;
      } else if ($c < 0xF5) {
        $out.= sprintf('\u%04x', (($c & 0x07) << 18) | ((ord($in{$i+ 1}) & 0x0F) << 12) | ((ord($in{$i+ 2}) & 0x3F) << 6) | (ord($in{$i+ 3}) & 0x3F));
        $i+= 3;
      }
    }
    
    return $out;
  }

  protected function representationOf($value) {
    if (null === $value) {
      return 'null';
    } else if (true === $value) {
      return 'true';
    } else if (false === $value) {
      return 'false';
    }

    $t= gettype($value);
    if ('string' === $t) {
      return '"'.$this->escape($value).'"';
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
    } else {
      throw new IllegalArgumentException('Cannot represent instances of '.typeof($value));
    }
  }

  /**
   * Writes a given value
   *
   * @param  var $value
   */
  public abstract function write($value);
}