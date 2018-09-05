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

* 获取JSAPI的支付参数（公众号、小程序支付）
```php
$wxpay = WechatPay::Jsapi($cfg);
$prepayId = $wxpay->getPrepayId($body, $orderNo, $amt, $openid);
$package = $wxpay->getPackage($prepayId);

```

* 获取APP的支付参数（APP支付）
```php
$wxpay = WechatPay::App($cfg);
$prepayId = $wxpay->getPrepayId($body, $orderNo, $amt, $openid);
$package = $wxpay->getPackage($prepayId);

```

* 获取H5支付URL（H5支付）
```php
$wxpay = WechatPay::Mweb($cfg);
$url = $wxpay->getMwebUrl($body,$orderNo,$amt);

```

* 获取扫码支付URL（扫码支付）
```php
$wxpay = WechatPay::Native($cfg);
$url = $wxpay->getCodeUrl($body,$out_trade_no,$total_fee,$product_id);

```

* 提交支付授权码（刷卡支付）
```php
$wxpay = WechatPay::Micro($cfg);
$url = $wxpay->microPay($body,$out_trade_no,$total_fee,$spbill_create_ip,$auth_code);

```
* 退款
```php
$wxpay = new WechatPay($cfg);
$result = $wxpay->refundByOutTradeNo($out_trade_no,$out_refund_no,$total_fee,$refund_fee);

```

* 企业付款
```php
$wxpay = WechatPay::Mchpay($cfg);
//付款到零钱
$result = $wxpay->transferWallet($partner_trade_no,$openid,$amount,$desc);
//付款到银行卡
$result = $wxpay->transferBankCard($partner_trade_no,$bank_no,$true_name,$bank_code,$amount,$desc);
```

* 红包
```php
$wxpay = WechatPay::Redpack($cfg);
//发放普通红包
$result = $wxpay->sendRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark)；
//发放裂变红包
$result = $wxpay->sendGroupRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark)；

```

* 代金券
```php
$wxpay = WechatPay::Coupon($cfg);
//发放代金券
$result = $wxpay->sendCoupon($coupon_stock_id,$open_id,$partner_trade_no)；
//查询代金券批次
$result = $wxpay->queryCouponStock($coupon_stock_id)；

```

