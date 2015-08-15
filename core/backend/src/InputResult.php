<?php

namespace WebCli;

class InputResult {
  // The original input string
  public $input;

  public $command;
  public $flags = array();
  public $options = array();
  public $args = array();

  public function __construct($input, $command, array $flags, array $options, array $args) {
    if (!is_string($input)) {
      throw new \Exception('$input parameter must be a string.');
    }
    if (!is_string($command)) {
      throw new \Exception('$command parameter must be a string.');
    }
    $this->input = $input;
    $this->command = $command;
    $this->flags = $flags;
    $this->options = $options;
    $this->args = $args;
  }
}