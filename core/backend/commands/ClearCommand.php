<?php

/**
 * Callback - clear
 */
class ClearCommand extends WebCli\SystemCommand {
	public function execute() {
		$this->setCallback('clearTerminal');
	}
}