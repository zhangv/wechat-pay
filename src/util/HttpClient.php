<?php
/**
 * HttpClient
 *
 * @license MIT
 * @author zhangv
 */
namespace zhangv\wechat\pay\util;

class HttpClient{

	const GET = 'get',POST = 'post', DELETE = 'delete',PUT = 'put';
	private $instance = null;
	private $errNo = null;
	private $info = null;
	private $timeout = 1;

	public function __construct($timeout = 1) {
		$this->initInstance($timeout);
	}

	public function initInstance($timeout){
		if(!$this->instance) {
			$this->instance = curl_init();
			curl_setopt($this->instance, CURLOPT_TIMEOUT, intval($timeout));
			curl_setopt($this->instance, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->instance, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->instance, CURLOPT_SSL_VERIFYPEER, false);
		}
	}

	public function get($url,$params = array(),$headers = array(),$opts = array()) {
		if (!$this->instance)	$this->initInstance($this->timeout);
		if($params && count($params) > 0) $url .= '?' . http_build_query($params);
		curl_setopt($this->instance, CURLOPT_URL, $url);
		curl_setopt($this->instance, CURLOPT_HTTPGET, true);
		curl_setopt($this->instance, CURLOPT_HTTPHEADER, $headers);
		curl_setopt_array($this->instance,$opts);
		$result = $this->execute();
		curl_close($this->instance);
		$this->instance = null;
		return $result;
	}

	public function post($url, $params = array(),$headers = array(),$opts = array()) {
		if (!$this->instance)	$this->initInstance($this->timeout);
		curl_setopt($this->instance, CURLOPT_URL, $url);
		curl_setopt($this->instance, CURLOPT_POST, true);
		curl_setopt($this->instance, CURLOPT_POSTFIELDS, $params);
		curl_setopt($this->instance, CURLOPT_HTTPHEADER, $headers);
		curl_setopt_array($this->instance,$opts);
		$result = $this->execute();
		curl_close($this->instance);
		$this->instance = null;
		return $result;
	}

	private function execute() {
		$result = curl_exec($this->instance);
		$this->errNo = curl_errno($this->instance);
		$this->info = curl_getinfo($this->instance);
		return $result;
	}

	public function getInfo(){
		return $this->info;
	}
}