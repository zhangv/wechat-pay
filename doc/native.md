# 扫码支付 

Step 1 - create QR
```php
<?
$cfg = include './config.php';
$payment = WechatPay::Native($cfg);
$orderid = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc";
$productid = "testproduct";
$amt = 1;
$codeurl = $payment->getCodeUrl($desc, $orderid, $amt,$productid);

?>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.qrcode.min.js"></script>
<div style="width:100%;text-align: center">
	<h3>请使用微信扫描下方的二维码</h3>
	<div id="qrcode"></div>
</div>

<script language="javascript">
	$(document).ready(function() {
		jQuery('#qrcode').qrcode({width: 200,height: 200,text: "<?=$codeurl?>"});
	});
</script>
```


Step 2 - handle the notification

[paidnotify.php](demo/paidnotify.php)


Step 3 - query order

```php
$cfg = include './config.php';
$payment = WechatPay::Native($cfg);
$result = $payment->queryOrderByOutTradeNo($orderno);
var_dump($result);

```