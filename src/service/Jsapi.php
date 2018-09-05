<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;

/**
 * 公众号支付
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
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
class Jsapi extends WechatPay {

	/**
	 * 获取预支付单信息(prepay_id)
	 * @param $body string 内容
	 * @param $out_trade_no string 商户订单号
	 * @param $total_fee int 总金额
	 * @param $openid string openid
	 * @param $spbill_create_ip
	 * @param $ext array
	 * @return string
	 * @throws \Exception
	 */
	public function getPrepayId($body,$out_trade_no,$total_fee,$openid,$spbill_create_ip = null,$ext = null) {
		$data = ($ext && is_array($ext))?$ext:array();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $spbill_create_ip?:$_SERVER["REMOTE_ADDR"];
		$data["notify_url"]   = $this->config["notify_url"];
		$data["trade_type"]   = WechatPay::TRADETYPE_JSAPI;
		if(!$openid) throw new Exception('openid is required when trade_type is JSAPI');
		$data["openid"]   = $openid;
		$result = $this->unifiedOrder($data);
		return $result["prepay_id"];
	}

	/**
	 * 获取支付参数
	 * @param $prepay_id string 预支付ID
	 * @return array
	 * @throws Exception
	 */
	public function getPackage($prepay_id) {
		$data = array();
		$data["package"]   = "prepay_id=$prepay_id";
		$data["timeStamp"] = time();
		$data["nonceStr"]  = $this->getNonceStr();
		$data["appId"] = $this->config["app_id"];
		$data["signType"]  = "MD5";
		$data["paySign"]   = $this->sign($data);
		return $data;
	}

}