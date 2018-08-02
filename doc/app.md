# APP支付 

Step 1 - provide server side API
```php
<?
$cfg = include './config.php';
$payment = WechatPay::App($cfg);
$stamp = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc$stamp";
$amt = 1;
$prepay_id = $payment->getPrepayId("$desc", "$stamp", $amt, 'ip', $ext);
$package = $payment->getPackage($prepay_id);
echo json_encode($package);

```

Step 2 - handle the payment in APP

...

Step 3 - handle the notification

[paidnotify.php](demo/paidnotify.php)


Step 4 - query order

```php
$cfg = include './config.php';
$payment = WechatPay::App($cfg);
$result = $payment->queryOrderByOutTradeNo($orderno);
var_dump($result);

```