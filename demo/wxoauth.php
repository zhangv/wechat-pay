<?php
$cfg = require './config.php';
$appid = $cfg['appid'];
$redirect = "http://{$_SERVER['HTTP_HOST']}/demo/wxoauthcallback.php";
$scope = 'snsapi_userinfo';
$state = "";
$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect&response_type=code&scope=$scope&state=$state#wechat_redirect";
$this->redirect($url);