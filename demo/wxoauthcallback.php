<?php

//OAuth授权回调
require_once __DIR__ . "/autoload.php";
use zhangv\wechat\pay\util\WechatOAuth;

if (isset($_GET['code'])){
	$cfg = require './config.php';

	$code = trim($_GET['code']);
	$state = trim($_GET['state']);
	$oauth = new WechatOAuth($cfg['app_id'],$cfg['app_secret']);
	$at = $oauth->authorize($code);

	if(!$at || empty($at->openid)){
		die('授权失败');
	}else{
		$accesstoken = $at->access_token;
		$openid = $at->openid;
		header('Location: '."http://{$_SERVER['HTTP_HOST']}/wechat-pay/demo/pay.php?openid=$openid");
	}
}else{
	die('授权失败，请重试。');
}