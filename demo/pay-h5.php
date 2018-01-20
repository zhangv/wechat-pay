<?php
//h5支付
require_once __DIR__ ."/../src/WechatPay.php";
use zhangv\wechat\WechatPay;

$cfg = include './config.php';

$payment = new WechatPay($cfg);
$stamp = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc$stamp";
$amt = 1;
$mweburl = $payment->getMwebUrl("$desc", "$stamp", $amt);

header('Location: '.$mweburl);