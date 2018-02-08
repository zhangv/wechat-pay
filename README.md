# WechatPay
[![Latest Stable Version](https://poser.pugx.org/zhangv/wechat-pay/v/stable)](https://packagist.org/packages/zhangv/wechat-pay)
[![License](https://poser.pugx.org/zhangv/wechat-pay/license)](https://packagist.org/packages/zhangv/wechat-pay)
[![Build Status](https://travis-ci.org/zhangv/wechat-pay.svg?branch=master)](https://travis-ci.org/zhangv/wechat-pay)

#### simplest, minimal dependency

### Install
```
composer require zhangv/wechat-pay
```
or

```
"zhangv/wechat-pay":"=1.3.1"
```

Step1 - Configuration
```php
return [
	'mch_id' => 'XXXX', //商户号
	'app_id' => 'XXXXXXXXXXXXXXXXXXX', //APPID
	'app_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXX', //App Secret
	'api_key' =>'XXXXXXXXXXXXXXXXXXXXXXX', //支付密钥
	'ssl_cert_path' => __DIR__ . '/keys/apiclient_cert.pem',
	'ssl_key_path' => __DIR__ .'/keys/apiclient_key.pem',
	'sign_type' => 'MD5',
	'notify_url' => 'http://YOURSITE/paidnotify.php',
	'refund_notify_url' => 'http://YOURSITE/refundednotify.php',
	'h5_scene_info' => [//required in H5
		'h5_info' => ['type' => 'Wap', 'wap_url' => 'http://wapurl', 'wap_name' => 'wapname']
	],
	'rsa_pubkey_path' => __DIR__ .'/keys/pubkey.pem',
	'jsapi_ticket' => __DIR__ .'/jsapi_ticket.json'
];
```
Step2 - Pay
```php
 require_once __DIR__ ."/../src/WechatPay.php";
 use zhangv\wechat\WechatPay;
 $cfg = include './config.php';
 
 if(empty( $_REQUEST['openid'])) {
 	$redirect = "http://{$_SERVER['HTTP_HOST']}/demo/wxoauth.php";
 	$this->redirect($url);
 	exit;
 }
 
 $payment = new WechatPay($cfg);
 $openid = $_REQUEST['openid'];
 $stamp = date('YmdHis');
 $ext = ['attach'=>''];
 $desc = "desc$stamp";
 $amt = 1;
 $prepay_id = $payment->getPrepayId("$desc", "$stamp", $amt, $openid, $ext);
 $package = $payment->getPackage($prepay_id);
 ?>
 <script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js" ></script>
 <script>
 	wx.ready(function(){
 		wx.chooseWXPay({
 			appId: '<?=$package["appId"]?>',
 			timestamp: <?=$package["timeStamp"]?>, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
 			nonceStr: '<?=$package["nonceStr"]?>', // 支付签名随机串，不长于 32 位
 			package: '<?=$package["package"]?>', // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
 			signType: '<?=$package["signType"]?>', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
 			paySign: '<?=$package["paySign"]?>', // 支付签名
 			success: function (res) {
 				// 支付成功后的回调函数
 				alert('支付成功！');
 			}
 		});
 	});
 	wx.error(function(res){// config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
 		alert(res);
 	});
 </script>
```