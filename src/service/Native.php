<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;
/**
 * 扫码支付
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_1
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
class Native extends WechatPay {

	/**
	 * 扫码支付(模式二)获取支付二维码
	 * @param $body
	 * @param $out_trade_no
	 * @param $total_fee
	 * @param $product_id
	 * @param $spbill_create_ip string 本地IP
	 * @param $ext array
	 * @return string
	 * @throws Exception
	 */
	public function getCodeUrl($body,$out_trade_no,$total_fee,$product_id,$spbill_create_ip = null,$ext = null){
		$data = ($ext && is_array($ext))?$ext:array();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $spbill_create_ip?:$_SERVER["SERVER_ADDR"];
		$data["notify_url"]   = $this->config["notify_url"];
		$data["trade_type"]   = self::TRADETYPE_NATIVE;
		if(!$product_id) throw new Exception('product_id is required when trade_type is NATIVE');
		$data["product_id"]   = $product_id;
		$result = $this->unifiedOrder($data);
		return $result["code_url"];
	}

}