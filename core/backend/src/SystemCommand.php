<?php

namespace WebCli;

abstract class SystemCommand extends Command {

  protected $terminal;

  public function setTerminal(Terminal $terminal) {
    $this->terminal = $terminal;
  }

}