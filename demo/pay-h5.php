<?php

//h5支付
require_once __DIR__ . "/autoload.php";
use zhangv\wechat\pay\WechatPay;

$cfg = include './config.php';

$payment = WechatPay::Mweb($cfg);
$stamp = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc$stamp";
$amt = 1;
$mweburl = $payment->getMwebUrl("$desc", "$stamp", $amt);

header('Location: '.$mweburl);