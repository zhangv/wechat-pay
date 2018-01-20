<?php
require_once __DIR__ ."/../src/WechatOAuth.php";
use zhangv\wechat\WechatOAuth;

$cfg = require './config.php';
$appid = $cfg['appid'];
$redirect = "http://{$_SERVER['HTTP_HOST']}/demo/wxoauthcallback.php";
$scope = 'snsapi_userinfo';
$state = "";
$oauth =new WechatOAuth($appid,$cfg['appsecret']);
$url = $oauth->authorizeURI($redirect,$scope);
$this->redirect($url);