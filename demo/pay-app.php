<?php

//APP支付 - 获取预支付交易回话标识
//注意：APP支付使用的是开放平台的APPID
require_once __DIR__ . "/autoload.php";

use zhangv\wechat\pay\WechatPay;

$cfg = include './config.php';
$payment = WechatPay::App($cfg);
$stamp = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc$stamp";
$amt = 1;
$prepay_id = $payment->getPrepayId("$desc", "$stamp", $amt, 'ip', $ext);
$package = $payment->getPackage($prepay_id);

echo json_encode($package);