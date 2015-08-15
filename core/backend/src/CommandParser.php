<?php

namespace WebCli;

/**
 * Class CommandParser
 */
class CommandParser implements ParserInterface {

  /**
   * Parses raw input and converts it into an InputResult object.
   *
   * @param $input The raw input from the command line
   * @returns InputResult
   */
  public function parse($input) {

    // split the input into an array based on spaces, but not if enclosed in double quotes
    // http://stackoverflow.com/questions/2202435/php-explode-the-string-but-treat-words-in-quotes-as-a-single-word
    preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $input, $matches);
    $params = $matches[0];

    $command = array_shift($params);

    $settings = $this->parseParams($params);

    $flags = $settings['flags'];
    $options = $settings['options'];
    $args = $settings['args'];

    $inputResult = new InputResult($input, $command, $flags, $options, $args);

    return $inputResult;
  }

  /**
   * Parse parameters gathered from input string.
   *
   * Questions:
   *  1. Should flags be able to be false?
   *  2. Can flags and options be stored together?
   *
   * @todo support long flags (e.g. ls --all)
   * @todo support long options (e.g. nslookup -type=ns)
   * @todo support option declaration without spaces (e.g. ssh -u user -pPassword user@host)
   *
   * This may not be the place for it but need to request additional arguments
   * from the user.
   *
   * Currently refactoring. It has some issues with removing the - from flags
   * and options when reconstructing the full command. Shows the wrong value
   * when prompts for input from the user.
   *
   * @param array $params
   * @return array
   */
  public function parseParams(array $params) {
    $flags = array();
    $options = array();
    $args = array();
    $numParams = sizeof($params);
    $argIndex = 0;
    for ($i = 0; $i < $numParams; $i++) {
      if (substr($params[$i], 0, 1) === '-') {
        // do we have multiple flags strung together?
        if (strlen($params[$i]) > 2) {
          // break flag argument into character array
          $chars = str_split($params[$i]);
          // remove the '-' from the beginning
          array_shift($chars);
          foreach ($chars as $flag) {
            $flags[] = $flag;
          }
        } else {
          // only one flag or option specified
          // remove the '-' from the beginning
          $char = substr($params[$i], 1, 1);

          // if there is another param, could be an argument
          if ($i + 1 < $numParams) {
            // need to look ahead and see if the next param is an argument
            if (substr($params[$i + 1], 0, 1) === '-') {
              // the next character is not an argument, assume this is a flag
              $flags[] = $char;
            } else {
              $options[$char] = $params[++$i];
            }
          } else {
            // this may be incorrect to assume that just because there
            // is no argument that it is a flag. what about prompting
            // for the argument?
            $flags[] = $char;
          }
        }
      } else {
        $args[$argIndex++] = $params[$i];
      }
    }

    return array(
      'flags' => $flags,
      'options' => $options,
      'args' => $args,
    );
  }

}