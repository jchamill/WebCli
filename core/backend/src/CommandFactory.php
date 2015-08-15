<?php

namespace WebCli;

class CommandFactory implements CommandFactoryInterface {
  /**
   * Creates a new instance of a Command and returns it.
   *
   * @param Terminal $terminal
   * @param InputResult $inputResult
   * @return mixed
   * @throws CommandNotFoundException
   */
  public function create(Terminal $terminal, InputResult $inputResult) {

    $commandDirectories = $terminal->getCommandDirectories();
    $systemCommandDirectories = $terminal->getSystemCommandDirectories();

    $class = ucfirst($inputResult->command) . 'Command';

    foreach ($commandDirectories as $dir) {
      $file = $dir . $class.'.php';

      if (file_exists($file)) {
        require_once($file);
        return new $class($inputResult);
      }
    }

    foreach ($systemCommandDirectories as $dir) {
      $file = $dir . $class.'.php';

      if (file_exists($file)) {
        require_once($file);
        return new $class($inputResult, $terminal);
      }
    }

    throw new CommandNotFoundException($inputResult->command . ': ' . \Config::get('invalidCmdMsg', 'command not found'));
  }
}