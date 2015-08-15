<?php

class Config {

  protected static $config = array();

  private function __construct() {}

  public static function set($key, $value) {
    self::$config[$key] = $value;
  }

  public static function get($key, $default = '') {
    return array_key_exists($key, self::$config) ? self::$config[$key] : $default;
  }
}