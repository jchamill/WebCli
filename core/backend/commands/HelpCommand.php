<?php

/**
 * Class HelpCommand
 */
class HelpCommand extends WebCli\SystemCommand {
  public function execute() {
    $output[] = $this->terminal->name . ' ' . $this->terminal->version;
    $output[] = '';

    $commandPaths = array();
    $systemCommandPaths = array();

    $commandDirs = $this->terminal->getcommandDirectories();
    $systemCommandDirs = $this->terminal->getSystemCommandDirectories();

    foreach ($commandDirs as $dir) {
      $commandPaths = array_merge($commandPaths, $this->_loadCommandsByDir($dir));
    }

    foreach ($systemCommandDirs as $dir) {
      $systemCommandPaths = array_merge($systemCommandPaths, $this->_loadCommandsByDir($dir));
    }

    foreach ($commandPaths as $class => $path) {
      require_once($path);
      $output[] = $class::help();
    }

    foreach ($systemCommandPaths as $class => $path) {
      require_once($path);
      $output[] = $class::help();
    }

    $this->setOutput(implode('<br />', $output));
  }

  private function _loadCommandsByDir($dir) {
    $paths = array();

    $files = scandir($dir);
    foreach ($files as $file) {
      if (!in_array($file, array('.', '..'))) {
        if (substr($file, 0, 1) !== '.') {
          $class = $this->_getClassFromFilename($file);
          $paths[$class] = $dir . $file;
        }
      }
    }

    return $paths;
  }

  private function _getClassFromFilename($filename) {
    return str_replace('.php', '', $filename);
  }
}