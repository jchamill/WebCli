<?php
/**
 *
 * Create authentication/permission helper class (determine roles, etc)
 *
 * Handle help message in base Command class based on command class settings.
 *
 *
 * DONE: Build ability to handle flags
 *
 * DONE: Allow commands to create the flags and options they support.
 *
 * DONE: Validate flags/arguments against command settings.
 *
 * DONE: Create session class to manage session and allow commands to interact
 * with session. This will be used for the cookie session used in requests.
 *
 * DONE: Might be worth extending Command class for authentication commands which
 * are commands that require the user to be logged in, boolean commands which
 * just return true or false, etc.
 */

//define('REST_URL', 'http://infection.creativeshampoo.com/api/rest/');
//define('WWW_ROOT', '/home/public_html/creativeshampoo.com/public');
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