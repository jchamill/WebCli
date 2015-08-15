<?php

namespace WebCli;

abstract class SystemCommand extends Command {

  protected $terminal;

  public function __construct(InputResult $inputResult, Terminal $terminal) {
    parent::__construct($inputResult);
    $this->terminal = $terminal;
  }

}