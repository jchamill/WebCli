<?php

namespace WebCli;

class ApiRequest {
	protected $url;
	protected $type = 'get';
	protected $data = array();
	protected $dataType = 'json';
	protected $cookie = FALSE;
	protected $cookie_session = FALSE;
	
	public function __construct($url = '') {
		if (!empty($url)) {
			$this->url = $url;
		}
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function setType($type) {
		$this->type = $type;
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function setCookie($session_name, $sessid) {
		$this->cookie = TRUE;
		$this->cookie_session = $session_name . '=' . $sessid;
	}
	
	public function send() {	
		if (!isset($this->url) || empty($this->url)) {	
			throw new \Exception('URL is required');
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->url);
		//accept json response
		if (strcasecmp($this->dataType, 'json') == 0) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		}
		if (strcasecmp($this->type, 'post') == 0) {
			//do a regular http post
			curl_setopt($curl, CURLOPT_POST, 1);
			//set post data
			if (isset($this->data) && !empty($this->data)) {
				curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
			}
		} else if (strcasecmp($this->type, 'delete') == 0) {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		//do not return header
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		//cookie session
		if ($this->cookie) {
			curl_setopt($curl, CURLOPT_COOKIE, $this->cookie_session);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
		
		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		//check if request successful
		if ($http_code == 200) {
			return $response;
		}	else {
			//get error message
			$http_message = curl_error($curl);
			return $http_message;
		}
	}
}