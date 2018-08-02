<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;

/**
 * 代金券
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/sp_coupon.php?chapter=12_1
 *
 */
class Coupon extends WechatPay {

	/**
	 * 发放代金券
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/sp_coupon.php?chapter=12_3&index=4
	 * @param $coupon_stock_id
	 * @param $open_id
	 * @param $partner_trade_no
	 * @param string $op_user_id
	 * @param array $ext
	 * @return array
	 */
	public function sendCoupon($coupon_stock_id,$open_id,$partner_trade_no,$op_user_id = '',$ext = array()){
		$data = (!empty($ext) && is_array($ext))?$ext:array();
		$data["partner_trade_no"] = $partner_trade_no;
		$data["coupon_stock_id"] = $coupon_stock_id;
		$data["openid_count"] = 1;
		$data["open_id"] = $open_id;
		$data["op_user_id"] = $op_user_id;
		$result = $this->post(self::URL_SEND_COUPON,$data,true);
		return $result;
	}

	/**
	 * 查询代金券批次
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/sp_coupon.php?chapter=12_4&index=5
	 * @param $coupon_stock_id
	 * @param string $op_user_id
	 * @return array
	 */
	public function queryCouponStock($coupon_stock_id,$op_user_id = ''){
		$data = array();
		$data["coupon_stock_id"] = $coupon_stock_id;
		$data["op_user_id"] = $op_user_id;
		$result = $this->post(self::URL_QUERY_COUPON_STOCK,$data,false);
		return $result;
	}

	/**
	 * 查询代金券信息
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/sp_coupon.php?chapter=12_5&index=6
	 * @param $coupon_id
	 * @param $open_id
	 * @param $stock_id
	 * @param string $op_user_id
	 * @param array $ext
	 * @return array
	 */
	public function queryCouponsInfo($coupon_id,$open_id,$stock_id,$op_user_id = '',$ext = array()){
		$data = (!empty($ext) && is_array($ext))?$ext:array();
		$data["coupon_id"] = $coupon_id;
		$data["stock_id"] = $stock_id;
		$data["open_id"] = $open_id;
		$data["op_user_id"] = $op_user_id;
		$result = $this->post(self::URL_QUERY_COUPON_INFO,$data,false);
		return $result;
	}

}