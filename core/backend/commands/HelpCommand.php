<?php

/**
 * Class HelpCommand
 */
class HelpCommand extends WebCli\SystemCommand {
	public function execute() {
    $output[] = 'Available Commands';
    $output[] = '';

    $commands = array();
    $systemCommands = array();

    $commandDirs = $this->terminal->getcommandDirectories();
    $systemCommandDirs = $this->terminal->getSystemCommandDirectories();

    foreach ($commandDirs as $dir) {
      $commands = array_merge($commands, $this->_loadCommandsByDir($dir));
    }

    foreach ($systemCommandDirs as $dir) {
      $systemCommands = array_merge($systemCommands, $this->_loadCommandsByDir($dir));
    }

    foreach ($commands as $cmd) {
      $output[] = $cmd;
    }

    foreach ($systemCommands as $cmd) {
      $output[] = $cmd;
    }

		$this->setOutput(implode('<br />', $output));
	}

  private function _loadCommandsByDir($dir) {
    $files = scandir($dir);
    return array_diff($files, array('.', '..'));
  }
}