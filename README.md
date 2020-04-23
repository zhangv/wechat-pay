# WechatPay - 微信支付
[![Latest Stable Version](https://poser.pugx.org/zhangv/wechat-pay/v/stable)](https://packagist.org/packages/zhangv/wechat-pay)
[![License](https://poser.pugx.org/zhangv/wechat-pay/license)](https://packagist.org/packages/zhangv/wechat-pay)
[![Build Status](https://travis-ci.org/zhangv/wechat-pay.svg?branch=master)](https://travis-ci.org/zhangv/wechat-pay)
[![codecov](https://codecov.io/gh/zhangv/wechat-pay/branch/master/graph/badge.svg)](https://codecov.io/gh/zhangv/wechat-pay)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zhangv/wechat-pay/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zhangv/wechat-pay/?branch=master)
[![Author](https://img.shields.io/badge/author-zhangv-green.svg)](https://zhangv.com)

#### simplest, minimal dependency

### Feature
* 使用简单
* 支持php-7x
* 不依赖第三方库（但依赖json、simpleXML、openssl、curl扩展）
* 100%测试覆盖
* 支持所有付款方式（公众号、APP、小程序、H5、扫码、刷卡）
* 支持企业付款、红包、代金券


### Install
```
composer require zhangv/wechat-pay
```
or

add:

```
"zhangv/wechat-pay":"1.4.2"
```
in composer.json

### Demo
*配置参数
```php
$cfg = [
    'mch_id'            => 'XXXX', //商户号
    'app_id'            => 'XXXXXXXXXXXXXXXXXXX', //APPID
    'app_secret'        => 'XXXXXXXXXXXXXXXXXXXXXXXXX', //App Secret
    'api_key'           =>'XXXXXXXXXXXXXXXXXXXXXXX', //支付密钥
    'ssl_cert_path'     => __DIR__ . '/../cert/apiclient_cert.pem',
    'ssl_key_path'      => __DIR__ .'/../cert/apiclient_key.pem',
    'sign_type'         => 'MD5',
    'notify_url'        => 'http://XXX.XXX/paidnotify.php',
    'refund_notify_url' => 'http://XXX.XXX/refundednotify.php',
    'h5_scene_info'     => [//required in H5
        'h5_info' => ['type' => 'Wap', 'wap_url' => 'http://wapurl', 'wap_name' => 'wapname']
    ],
    'rsa_pubkey_path'   => __DIR__ .'/../cert/pubkey.pem',
    'jsapi_ticket'      => __DIR__ .'/jsapi_ticket.json' //jsticket的临时存放路径
];

```

* 获取公众号支付参数（公众号网页支付）
```php
$wp = WechatPay::Jsapi($cfg);
$prepayId = $wp->getPrepayId($body, $orderNo, $amt, $openid);
$package = $wp->getPackage($prepayId);

```
* 获取小程序支付参数（小程序支付）
```php
$wp = WechatPay::Weapp($cfg);
$prepayId = $wp->getPrepayId($body, $orderNo, $amt, $openid);
$package = $wp->getPackage($prepayId);

```

* 获取APP的支付参数（APP支付）
```php
$wp = WechatPay::App($cfg);
$prepayId = $wp->getPrepayId($body, $orderNo, $amt, $openid);
$package = $wp->getPackage($prepayId);

```

* 获取H5支付URL（H5支付）
```php
$wp = WechatPay::Mweb($cfg);
$url = $wp->getMwebUrl($body,$orderNo,$amt);

```

* 获取扫码支付URL（扫码支付）
```php
$wp = WechatPay::Native($cfg);
$url = $wp->getCodeUrl($body,$out_trade_no,$total_fee,$product_id);

```

* 提交支付授权码（刷卡支付）
```php
$wp = WechatPay::Micro($cfg);
$url = $wp->microPay($body,$out_trade_no,$total_fee,$spbill_create_ip,$auth_code);

```

* 支付结果后台通知处理
```php
$notifyxml = file_get_contents("php://input");
$wp = new WechatPay($cfg);
$msg = $wp->onPaidNotify($notifyxml, function($notifyArray){
    //处理逻辑
    return 'ok';
});
$xml = "<xml>
           <return_code>SUCCESS</return_code>
           <return_msg><![CDATA[$msg]]></return_msg>
        </xml>";
echo $xml;
```


* 退款
```php
$wxpay = new WechatPay($cfg);
$result = $wxpay->refundByOutTradeNo($out_trade_no,$out_refund_no,$total_fee,$refund_fee);

```

* 企业付款
```php
$wp = WechatPay::Mchpay($cfg);
//付款到零钱
$result = $wp->transferWallet($partner_trade_no,$openid,$amount,$desc);
//付款到银行卡
$result = $wp->transferBankCard($partner_trade_no,$bank_no,$true_name,$bank_code,$amount,$desc);
```

* 红包
```php
$wp = WechatPay::Redpack($cfg);
//发放普通红包
$result = $wp->sendRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark)；
//发放裂变红包
$result = $wp->sendGroupRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark)；

```

* 代金券
```php
$wp = WechatPay::Coupon($cfg);
//发放代金券
$result = $wp->sendCoupon($coupon_stock_id,$open_id,$partner_trade_no)；
//查询代金券批次
$result = $wp->queryCouponStock($coupon_stock_id)；

```

