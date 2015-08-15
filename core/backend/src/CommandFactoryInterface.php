<?php

namespace WebCli;

interface CommandFactoryInterface {
  public function create(Terminal $terminal, InputResult $inputResult);
}