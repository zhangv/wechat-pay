<?php
require_once __DIR__ ."/../src/WechatOAuth.php";
use zhangv\wechat\WechatOAuth;

if (isset($_GET['code'])){
	$cfg = require './config.php';

	$code = trim($_GET['code']);
	$state = trim($_GET['state']);
	$oauth = new WechatOAuth($cfg['appid'],$cfg['appsecret']);
	$at = $oauth->authorize($code);

	if(!$at || empty($at->openid)){
		die('授权失败');
	}else{
		$accesstoken = $at->access_token;
		$openid = $at->openid;
		$this->redirect("http://{$_SERVER['HTTP_HOST']}/demo/pay.php?openid=$openid");
	}
}else{
	die('授权失败，请重试。');
}