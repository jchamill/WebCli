<?php

/**
 * Class ExampleCommand
 */
class ExampleCommand extends WebCli\Command {

	public function init() {

    // Prompt for additional input
		$this->addFlag('p');

    // Mask the prompt input
    $this->addFlag('m');

    // Display output in uppercase
    $this->addFlag('u');

    // Use Session to persist prepend
    $this->addFlag('s');

    // Clear Session prepend value
    $this->addFlag('c');

    // Add a required option to append to the output
    $this->addOption('a');

    // Add an optional option to prepend to the output
    $this->addOption('b', array(
      'required' => FALSE,
    ));
	}

  public function execute() {

    // Read the first required argument (this is not an option)
    $output = $this->getArgument(0);

    // Check to see if the "p" flag was set
    $showPrompt = $this->getFlag('p');
    if ($showPrompt) {
      // Check if the "m" flag was set to mask the output
      $isMasked = $this->getFlag('m');

      // Prompt the user for input, second parameter determines if the input field is masked
      $output .= $this->read('Enter a value:', $isMasked);
    }

    // Check to see if the "u" flag was set
    $isCap = $this->getFlag('u');

    if ($isCap) {
      $output = strtoupper($output);
    }

    // This option is required so we do not have to make sure it was set
    $append = $this->getOption('a');

    // Throwing an exception will terminate command and send message to terminal
    if (is_numeric($append)) {
      throw new Exception('error: -a option cannot be numeric');
    }

    $output .= $append;

    if ($this->getFlag('c')) {
      WebCli\Session::clear('ExampleSave');
    }

    // This option is optional so we must make sure it was set
    $prepend = $this->getOption('b');

    if ($prepend) {
      if ($this->getFlag('s')) {
        WebCli\Session::write('ExampleSave', $prepend);
      }
      $output = $prepend . $output;
    } else {
      $saved = WebCli\Session::read('ExampleSave');
      if (isset($saved)) {
        $output = $saved . $output;
      }
    }

    $this->setOutput($output);

	}

}