<?php

namespace WebCli;

abstract class RemoteCommand extends Command {
  protected $requestUrl;
  protected $allowedGroups = array();
  protected $Request;

  public function __construct(InputResult $inputResult) {
    $this->Request = new ApiRequest();
    parent::__construct($inputResult);
  }

  public function setRequestUrl($url) {
    $this->Request->setUrl($url);
  }

  public function setRequestType($type) {
    $this->Request->setType($type);
  }

  public function setRequestData($data) {
    $this->Request->setData($data);
  }

  public function sendRequest() {
    return $this->Request->send();
  }

  public function beforeExecute() {
    if (!empty($this->allowedGroups)) {
      $sessid = Session::read('sessid');
      $session_name = Session::read('session_name');
      if (isset($sessid) && isset($session_name)) {
        $this->Request->setCookie($session_name, $sessid);
      } else {
        $this->error = 'You are not logged in!';
      }
    }
  }
}