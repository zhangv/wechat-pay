<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;
/**
 * H5支付
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_20&index=1
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
class Mweb extends WechatPay {

	/**
	 * H5支付获取支付跳转链接
	 * @param $body string 商品描述
	 * @param $out_trade_no string 商户订单号
	 * @param $total_fee int 总金额(分)
	 * @param $ext array
	 * @return string
	 * @throws Exception
	 */
	public function getMwebUrl($body,$out_trade_no,$total_fee,$ext = null){
		$data = ($ext && is_array($ext))?$ext:array();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:'';
		$data["notify_url"]   = $this->config["notify_url"];
		$data["trade_type"]   = self::TRADETYPE_MWEB;
		if(!isset($this->config['h5_scene_info'])) throw new Exception('h5_scene_info should be configured');
		$data["scene_info"]   = json_encode($this->config['h5_scene_info']);
		$result = $this->unifiedOrder($data);
		return $result["mweb_url"];
	}

}