<?php namespace text\json;

use lang\Enum;

/** Json types enumeration */
class Types extends Enum {
  public static $STRING, $DOUBLE, $INT, $NULL, $FALSE, $TRUE, $ARRAY, $OBJECT;

  /** @return bool */
  public function isArray() { return self::$ARRAY === $this; }

  /** @return bool */
  public function isObject() { return self::$OBJECT === $this; }
}