<?php

//OAuth授权
require_once __DIR__ . "/autoload.php";

use zhangv\wechat\pay\util\WechatOAuth;

$cfg = require './config.php';
$appid = $cfg['app_id'];
$redirect = "http://{$_SERVER['HTTP_HOST']}/wechat-pay/demo/wxoauthcallback.php";
$scope = 'snsapi_userinfo';
$state = "";
$oauth =new WechatOAuth($appid,$cfg['app_secret']);
$url = $oauth->authorizeURI($redirect,$scope);

header('Location: '.$redirect);