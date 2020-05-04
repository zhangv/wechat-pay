<?php

//扫码支付
require_once __DIR__ . "/autoload.php";
use zhangv\wechat\pay\WechatPay;

$cfg = include './config.php';
$payment = WechatPay::Native($cfg);
$orderid = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc";
$productid = "testproduct";
$amt = 1;
$codeurl = $payment->getCodeUrl($desc, $orderid, $amt,$productid);

?>
<script type="text/javascript" src="//cdn.bootcdn.net/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
<script type="text/javascript" src="//cdn.bootcdn.net/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<div style="width:100%;text-align: center">
	<h3>请使用微信扫描下方的二维码</h3>
	<div id="qrcode"></div>
</div>

<script language="javascript">
	$(document).ready(function() {
		jQuery('#qrcode').qrcode({width: 200,height: 200,text: "<?=$codeurl?>"});
	});
</script>