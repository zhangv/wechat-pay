<?php

require_once __DIR__ ."/../WechatPay.php";

if (isset($_GET['code'])){
	$code = trim($_GET['code']);
	$state = trim($_GET['state']);
	$at = getOauthAccessToken($code);
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

function getOauthAccessToken($code){
	$cfg = include './config.php';
	$appid = $cfg['appid'];
	$appsecret = $cfg['appsecret'];
	$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
	$ch=curl_init($url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
	$output=curl_exec($ch);
	curl_close($ch);
	$r = json_decode($output);
	return $r;
}