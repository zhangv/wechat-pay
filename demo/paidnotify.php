<?php

require_once __DIR__ ."/../src/WechatPay.php";
use zhangv\wechat\WechatPay;

$xml = file_get_contents("php://input");

$cfg = require './config.php';
$payment = new WechatPay($cfg);
$payment->onPaidNotify($xml,function($notifydata) use ($payment){
	//do stuff
	print_r($notifydata);
	$payment->responseNotify('SUCCESS','OK');
});

