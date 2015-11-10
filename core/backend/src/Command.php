<?php

namespace WebCli;

/**
 * Class Command
 *
 * This is the core class that all commands will inherit from.
 */
abstract class Command {

  // The original input (full command string)
  protected $input;

  // The command
  protected $command;

  // Command options from input
  protected $options;

  // Available options from command configuration
  protected $_options  = array();

  // Command flags from input
  protected $flags;

  // Available flags from command configuration
  protected $_flags = array();

  // Arguments that were read from the prompt
  protected $promptArgs = array();

  // Current index of the argument we are reading
  protected $promptArgIndex = 0;

  // Stores the output of the command (@todo might need some refactoring)
  protected $output;

  // Determines if an error was encountered (@todo is this necessary?)
  protected $error;

  /**
   * Need an overloaded constructor so we can instantiate commands
   * in order to display help information about the command and also
   * instantiate the command from and InputResult. This is empty so
   * we can provide static methods to return the instance.
   */
  protected function __construct() {}

  /**
   * Return an instance of the command by passing an InputResult.
   * This method is used when creating commands from a factory.
   *
   * @param InputResult $inputResult
   * @return static
   */
  public static function createFromInput(InputResult $inputResult) {
    $instance = new static();
    $instance->initialize($inputResult);
    return $instance;
  }

  /**
   * Initialize the command from an InputResult.
   *
   * @param InputResult $inputResult
   */
  protected function initialize(InputResult $inputResult) {
    $this->input = $inputResult->input;
    $this->command = $inputResult->command;
    $this->options = $inputResult->options;
    $this->flags = $inputResult->flags;
    $this->args = $inputResult->args;

    // @todo inject an output class so different outputs can be used
    $this->output = new \stdClass;
    $this->output->commandState = 'initialized';

    $this->error = FALSE;

    $this->init();
    $this->validate();
  }

  /**
   * Output the help based on the configuration of available
   * options and flags.
   *
   * @todo provide better option output and support arguments
   * if they are available. Currently, these are not set in init
   * but called directly from execute, so may need to refactor.
   */
  public static function help() {
    $output = array();

    // Only need to create an instance of the command, not passing
    // an InputResult since we are only getting info about the command.
    $instance = new static();

    // Call init so the flags and options will be set
    $instance->init();

    // Get the actual command by converting the class name
    $output[] = strtolower(str_replace('Command', '', get_class($instance)));

    // Show available flags
    if (!empty($instance->_flags)) {
      $output[] = '[-' . implode('', $instance->_flags) . ']';
    }

    // Show available options
    if (!empty($instance->_options)) {
      foreach ($instance->_options as $option => $config) {
        $option_help = '[-' . $option . ' option]';
        // required key is not explicitly set, if it doesn't exist it is required
        // required key must be explicitly set to FALSE for option to be optional
        if (!array_key_exists('required', $config) || $config['required'] === TRUE) {
          $option_help .= '*';
        }
        $output[] = $option_help;
      }
    }

    return implode(' ', $output);
  }

  /**
   * Add an allowed flag to the command. The primary use for this is used
   * in the init function of the command class to specify the commands flags.
   *
   * @param $flag
   */
  protected function addFlag($flag) {
    array_push($this->_flags, $flag);
  }

  /**
   * Determine if a flag is set. If the flag is set, returns TRUE. Otherwise
   * it will return false.
   *
   * @param $flag
   * @return bool
   */
  protected function getFlag($flag) {
    return in_array($flag, $this->flags);
  }

  /**
   * Add an allowed option to the command. The primary use for this is used
   * in the init function of the command class to specify the command options.
   *
   * @param $option
   * @param array $config
   */
  public function addOption($option, $config = array()) {
    $this->_options[$option] = $config;
  }

  /**
   * @todo Figure out if this is necessary
   *
   * @param $option
   * @return bool|mixed
   */
  public function getOptionIndex($option) {
    $keys = array_keys($this->_options);
    $index = array_search($option, $keys);
    if (array_key_exists($index, $this->options)) {
      return $index;
    }
    return FALSE;
  }

  /**
   * Returns the value of an option.
   *
   * @param $option
   * @return bool|mixed
   * @throws \Exception
   */
  public function getOption($option) {
    if (!array_key_exists($option, $this->options)) {
      if (false !== ($default_option_index = $this->getOptionIndex($option))) {
        $option = $default_option_index;
        //need to check index exists in options?
      } else if (array_key_exists('required', $this->_options[$option]) && !$this->_options[$option]['required']) {
        return false; // @todo set a variable and return at the end of function
      } else {
        throw new \Exception('missing required option -' . $option);
      }
    }
    return $this->options[$option];
  }

  /**
   * Set the arguments that were read from the prompt.
   *
   * @param $promptArgs
   */
  public function setPromptArgs($promptArgs) {
    $this->promptArgs = $promptArgs;
  }

  /**
   * Returns the first prompt arg. If multiple read() calls are made in the
   * command, this will get them in order. If a read() call is made and the
   * promptArg is not available at that index, we return FALSE.
   *
   * @return bool
   */
  public function getPromptArg() {
    return array_key_exists($this->promptArgIndex, $this->promptArgs) ? $this->promptArgs[$this->promptArgIndex++] : FALSE;
  }

  /**
   * Read from the terminal input. If the prompt arg is not yet available,
   * we send a request to the terminal to read from the prompt.
   *
   * @param string $output
   * @param bool $masked
   * @return bool|mixed
   */
  public function read($output = '', $masked = FALSE) {
    if (!$arg = $this->getPromptArg()) {
      $this->setMasked($masked);
      $this->setOutput($output);
      $this->setCallback('readFromPrompt');
      $this->setState('readingArguments');
      // Send the original command back so we can call the command again but pass the read argument
      // Also send the current prompt arguments back so we can add to them
      $this->setCallbackArgs(array($this->input, $this->promptArgs));
      $this->returnOutput();
    }

    return $arg;
  }

  /**
   * Assumes all arguments are required.
   *
   * I added this but maybe we just need to use options that are
   * not indexed by a char, these will default to an int index.
   *
   * @todo get rid of args, just use options
   */
  public function getArgument($index = 0) {
    if (!array_key_exists($index, $this->args)) {
      throw new \Exception('error: argument missing');
    }
    return $this->args[$index];
  }

  /**
   * Validate the command based on flags and options from input.
   */
  public function validate() {
    // are set flags ($this->_flags) in available flags ($this->flags)
    $invalidFlags = array_diff($this->flags, $this->_flags);
    if (!empty($invalidFlags)) {
      throw new \Exception('invalid flags: ' . implode(', ', $invalidFlags));
    }

    // are set options ($this->_options) in available options ($this->options)
    $invalidOptions = array_diff_key($this->options, $this->_options);
    if (!empty($invalidOptions)) {
      throw new \Exception('invalid options: ' . implode(', ', array_keys($invalidOptions)));
    }
  }

  /**
   * Set the ouput text to be sent to the terminal.
   *
   * @param $output
   */
  public function setOutput($output) {
    $this->output->responseText = (string)$output;
  }

  /**
   * Set the masked variable that will be sent to the terminal. If this
   * is true, the input on the terminal will show asterisks instead
   * of the text. Useful for protecting sensitive data that is typed in
   * the terminal.
   *
   * @param $masked
   */
  public function setMasked($masked) {
    $this->output->masked = $masked;
  }

  /**
   * Set the command state that will be sent to the terminal.
   *
   * @param $state
   */
  public function setState($state) {
    $this->output->commandState = $state;
  }

  /**
   * Set the output callback function that will be sent to the terminal.
   *
   * @param $callback
   */
  public function setCallback($callback) {
    $this->output->callback = $callback;
  }

  /**
   * Set the callback arguments that will be sent to the terminal.
   *
   * @param $args
   */
  public function setCallbackArgs($args) {
    $this->output->callbackArgs = $args;
  }

  /**
   * Runs the command.
   */
  public function run() {
    $this->beforeExecute();
    $this->execute();
    $this->afterExecute();
    $this->setState('completed');
    $this->returnOutput();
  }

  /**
   * Returns the output in json format.
   *
   * @todo This seems like it is the wrong approach but maybe not since
   * all commands talk to our terminal app (frontend). And our terminal
   * app speaks json and nothing else.
   *
   */
  public function returnOutput() {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json; charset=UTF-8');
    print json_encode($this->output);
    exit();
  }

  /**
   * Initialize the command. This is used to set the command flags
   * and options available.
   *
   * @todo Is it possible to not init a command?
   */
  public function init() {}

  /**
   * A hook that will be called before the command is executed.
   */
  public function beforeExecute() {}

  /**
   * @return mixed
   */
  abstract public function execute();

  /**
   * A hook that will be called after the command is executed.
   */
  public function afterExecute() {}
}