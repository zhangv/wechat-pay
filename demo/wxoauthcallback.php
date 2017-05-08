<?php

if (isset($_GET['code'])){
	$code = trim($_GET['code']);
	$state = trim($_GET['state']);
	$at = $this->getOauthAccessToken($code);
	if(!$at || empty($at->openid)){
		die('授权失败');
	}else{
		$accesstoken = $at->access_token;
		$openid = $at->openid;
		$this->redirect("http://{$_SERVER['HTTP_HOST']}/demo/wxpay.php?openid=$openid");
	}
}else{
	die('授权失败，请重试。');
}