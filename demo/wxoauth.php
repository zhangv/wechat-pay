<?php

//OAuth授权
require_once __DIR__ ."/../src/WechatOAuth.php";
require_once __DIR__ ."/../src/HttpClient.php";
use zhangv\wechat\WechatOAuth;

$cfg = require './config.php';
$appid = $cfg['app_id'];
$redirect = "http://{$_SERVER['HTTP_HOST']}/wechat-pay/demo/wxoauthcallback.php";
$scope = 'snsapi_userinfo';
$state = "";
$oauth =new WechatOAuth($appid,$cfg['app_secret']);
$url = $oauth->authorizeURI($redirect,$scope);

header('Location: '.$redirect);