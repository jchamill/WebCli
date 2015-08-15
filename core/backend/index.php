<?php

define('CORE_ROOT', dirname(dirname(__FILE__)));
define('CLI_ROOT', CORE_ROOT . '/backend');

// Can't autoload this cause it will require a namespace and the file
// also needs to be manually required in /core/backend/backbone/config.php
// and having the namespace declared doesn't work
require_once(CLI_ROOT . '/shared/Config.php');

// Use composer to autoload
require_once(CORE_ROOT . '/../vendor/autoload.php');

// Session will allow commands/terminal to maintain state
// @todo Are we okay with having static session calls in other classes, try to decouple if possible
WebCli\Session::start();
error_reporting(E_ALL);

if (!isset($_REQUEST['input']) && !isset($_REQUEST['state'])) {
  // @todo need to create a standard for handling these error responses
  die(json_encode(array('commandState' => 'error', 'responseText' => 'improper access')));
}

// @todo This feels a little dirty, try to make it prettier
$args = (isset($_REQUEST['args'])) ? $_REQUEST['args'] : array();

$parser = new WebCli\CommandParser();
$commandFactory = new WebCli\CommandFactory();
$terminal = new WebCli\Terminal($parser, $commandFactory);

// Set command directories
$terminal->registerCommandDirectory(CORE_ROOT . '/../commands/');
$terminal->registerSystemCommandDirectory(CLI_ROOT . '/commands/');

$terminal->execute($_REQUEST['input'], $_REQUEST['state'], $args);