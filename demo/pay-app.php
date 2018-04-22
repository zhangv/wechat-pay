<?php

//APP支付 - 获取预支付交易回话标识
//注意：APP支付使用的是开放平台的APPID
require_once __DIR__ . '/../src/WechatPay.php';
use zhangv\wechat\WechatPay;

$cfg = include './config.php';
$payment = new WechatPay($cfg);
$stamp = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc$stamp";
$amt = 1;
$prepay_id = $payment->getPrepayIdAPP("$desc", "$stamp", $amt, 'ip', $ext);
$package = $payment->getPackage($prepay_id);

echo json_encode($package);