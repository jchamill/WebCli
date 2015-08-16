<?php

namespace WebCli;

/**
 * Class Terminal
 */
class Terminal {

  public $name = 'WebCLI';
  public $version = 'beta-0.0.1';

  public $parser;
  public $commandFactory;

  private $commandDirs;
  private $systemCommandDirs;

  public function __construct(ParserInterface $parser, CommandFactoryInterface $commandFactory) {
    $this->parser = $parser;
    $this->commandFactory = $commandFactory;
  }

  /**
   * @param $input The full command string from the frontend
   * @param $state The state of the command from the frontend
   * @param array $args Arguments that are captured using @see command->read()
   * @return string
   */
  public function execute($input, $state, array $args) {
    $output = '';

    $inputResult = $this->processInput($input);

    try {
      $command = $this->commandFactory->create($this, $inputResult);
    } catch (CommandNotFoundException $e) {
      // Probably doesn't make since to have specific Exception classes since we handle them all the same way
      die(json_encode(array('commandState' => 'error', 'responseText' => $e->getMessage())));
    } catch (\Exception $e) {
      die(json_encode(array('commandState' => 'error', 'responseText' => $e->getMessage())));
    }

    if ($state === 'readingArguments' && !empty($args)) {
      $command->setPromptArgs($args);
    }

    try {
      $output = $command->run();
    } catch (\Exception $e) {
      die(json_encode(array('commandState' => 'error', 'responseText' => $e->getMessage())));
    }

    return $output;
  }

  public function processInput($input) {
    return $this->parser->parse($input);
  }

  public function getCommandDirectories() {
    return $this->commandDirs;
  }

  public function getSystemCommandDirectories() {
    return $this->systemCommandDirs;
  }

  public function registerCommandDirectory($path) {
    $this->commandDirs[$path] = $path;
  }

  public function registerSystemCommandDirectory($path) {
    $this->systemCommandDirs[$path] = $path;
  }


}