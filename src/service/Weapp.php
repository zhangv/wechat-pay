<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;

/**
 * 小程序支付
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1
 *
 * @method mixed queryOrderByOutTradeNo($out_trade_no)
 * @method mixed queryOrderByTransactionId($transaction_id)
 * @method mixed closeOrder($out_trade_no)
 * @method mixed refundByOutTradeNo($out_trade_no, $out_refund_no, $total_fee, $refund_fee, $ext = array())
 * @method mixed refundByTransactionId($transaction_id, $out_refund_no, $total_fee, $refund_fee, $ext = array())
 * @method mixed queryRefundByOutRefundNo($out_refund_no, $offset = 0)
 * @method mixed queryRefundByOutTradeNo($out_trade_no, $offset = 0)
 * @method mixed queryRefundByRefundId($refund_id, $offset = 0)
 * @method mixed queryRefundByTransactionId($transaction_id, $offset = 0)
 * @method mixed downloadBill($bill_date, $bill_type = 'ALL')
 * @method mixed downloadFundFlow($bill_date, $account_type = WechatPay::ACCOUNTTYPE_BASIC, $tar_type = 'GZIP')
 * @method mixed onPaidNotify($notify_data, callable $callback = null)
 * @method mixed onRefundedNotify($notify_data, callable $callback = null)
 * @method mixed report($interface_url, $execution_time, $return_code, $result_code, $user_ip, $out_trade_no = null, $time = null, $device_info = null,$return_msg = null,$err_code = null,$err_code_des = null)
 * @method mixed batchQueryComment($begin_time, $end_time, $offset = 0, $limit = 200)
 */
class Weapp extends Jsapi {

}