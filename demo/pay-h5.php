<?php
//h5支付
require_once __DIR__ ."/../WechatPay.php";
$cfg = include './config.php';

$payment = new WechatPay($cfg);
$stamp = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc$stamp";
$amt = 1;
$mweburl = $payment->getMwebUrl("$desc", "$stamp", $amt);

header('Location: '.$mweburl);