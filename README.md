# WechatPay - 微信支付
[![Latest Stable Version](https://poser.pugx.org/zhangv/wechat-pay/v/stable)](https://packagist.org/packages/zhangv/wechat-pay)
[![License](https://poser.pugx.org/zhangv/wechat-pay/license)](https://packagist.org/packages/zhangv/wechat-pay)
[![Build Status](https://travis-ci.org/zhangv/wechat-pay.svg?branch=master)](https://travis-ci.org/zhangv/wechat-pay)
[![codecov](https://codecov.io/gh/zhangv/wechat-pay/branch/master/graph/badge.svg)](https://codecov.io/gh/zhangv/wechat-pay)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zhangv/wechat-pay/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zhangv/wechat-pay/?branch=master)
[![Author](https://img.shields.io/badge/author-zhangv-green.svg)](https://zhangv.com)

#### simplest, minimal dependency

### Install
```
composer require zhangv/wechat-pay
```
or

add:

```
"zhangv/wechat-pay":"1.3.11"
```
in composer.json

### Demo

获得JSAPI的支付参数
```php
$payment = WechatPay::Jsapi($cfg);
$prepayId = $payment->getPrepayId($body, $orderNo, $amt, $openid);
$package = $payment->getPackage($prepayId);

```

获得APP的支付参数
```php
$payment = WechatPay::App($cfg);
$prepayId = $payment->getPrepayId($body, $orderNo, $amt, $openid);
$package = $payment->getPackage($prepayId);

```

获取H5支付URL
```php
$payment = WechatPay::Mweb($cfg);
$url = $payment->getMwebUrl($body,$orderNo,$amt);

```

获取扫码支付URL
```php
$payment = WechatPay::Mweb($cfg);
$url = $payment->getCodeUrl($body,$out_trade_no,$total_fee,$product_id);

```

退款
```php
$payment = new WechatPay($cfg);
$result = $payment->refundByOutTradeNo($out_trade_no,$out_refund_no,$total_fee,$refund_fee);

```
