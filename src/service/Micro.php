<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;
/**
 * 刷卡支付
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1
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
 * @method mixed shortUrl($longurl)
 * @method mixed batchQueryComment($begin_time, $end_time, $offset = 0, $limit = 200)
 */
class Micro extends WechatPay {

	/**
	 * 提交刷卡支付
	 * @param $body
	 * @param $out_trade_no
	 * @param $total_fee
	 * @param $spbill_create_ip
	 * @param $auth_code
	 * @param array $ext
	 * @return array
	 * @throws Exception
	 */
	public function microPay($body,$out_trade_no,$total_fee,$spbill_create_ip,$auth_code,$ext = array()){
		$data = (!empty($ext) && is_array($ext))?$ext:array();
		$data["appid"] = $this->config["app_id"];
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $spbill_create_ip;
		$data["auth_code"] = $auth_code;
		return $this->post(self::URL_MICROPAY,$data,true);
	}

	/**
	 * 授权码查询openid
	 * @param $auth_code
	 * @return mixed
	 * @throws Exception
	 */
	public function authCodeToOpenId($auth_code){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["auth_code"] = $auth_code;
		return $this->post(self::URL_AUTHCODETOOPENID,$data,false);
	}


	/**
	 * 撤销订单 - 使用商户订单号
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 * @throws Exception
	 */
	public function reverseByOutTradeNo($out_trade_no){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["out_trade_no"] = $out_trade_no;
		return $this->post(self::URL_REVERSE, $data,true);
	}

	/**
	 * 撤销订单 - 使用微信订单号
	 * @param $transaction_id string 微信订单号
	 * @return array
	 * @throws Exception
	 */
	public function reverseByTransactionId($transaction_id){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["transaction_id"] = $transaction_id;
		return $this->post(self::URL_REVERSE, $data,true);
	}
}