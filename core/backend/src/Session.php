<?php

namespace WebCli;

class Session {
	private static $_initialized = FALSE;
	public static function start() {
		if (!self::$_initialized) {
			if (headers_sent()) {
				throw new \Exception('error: headers already sent');
			}
			session_start();
		}
	}
	
	public static function read($key) {
		if (array_key_exists($key, $_SESSION)) {
			return $_SESSION[$key];
		}
		//returning false could be misleading
		return NULL;
	}
	
	public static function write($key, $data) {
		$_SESSION[$key] = $data;
	}

  public static function clear($key) {
    unset($_SESSION[$key]);
  }
	
	public static function destroy() {
		$_SESSION = array();
		session_destroy();
	}
}