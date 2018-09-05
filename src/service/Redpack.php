<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;

/**
 * 现金红包
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_1
 *
 */
class Redpack extends WechatPay {

	/**
	 * 发放普通红包
	 * @param $mch_billno string 商户订单号
	 * @param $send_name string 商户名称
	 * @param $re_openid string 用户openid
	 * @param $total_amount int 付款金额 单位分
	 * @param $total_num int 红包发放总人数
	 * @param $wishing string 红包祝福语
	 * @param $act_name string 活动名称
	 * @param $remark string 备注
	 * @param $scene_id string 场景id,发放红包使用场景，红包金额大于200时必传 PRODUCT_1:商品促销 PRODUCT_2:抽奖 PRODUCT_3:虚拟物品兑奖 PRODUCT_4:企业内部福利 PRODUCT_5:渠道分润 PRODUCT_6:保险回馈 PRODUCT_7:彩票派奖 PRODUCT_8:税务刮奖
	 * @param $riskinfo string 活动信息
	 * @param $consume_mch_id string 资金授权商户号
	 * @return array
	 * @throws Exception
	 */
	public function sendRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark,$scene_id = '',$riskinfo = '',$consume_mch_id = ''){
		$data = array();
		$data["wxappid"] = $this->config["app_id"];
		$data["mch_billno"] = $mch_billno;
		$data["send_name"] = $send_name;
		$data["re_openid"] = $re_openid;
		$data["total_amount"] = $total_amount;
		if($total_amount > 20000 && trim($scene_id)=='') throw new Exception("scene_id is required when total_amount beyond 20000");
		$data["total_num"] = $total_num;
		$data["wishing"] = $wishing;
		$data["act_name"] = $act_name;
		$data["remark"] = $remark;
		$data["scene_id"] = $scene_id;
		$data["riskinfo"] = $riskinfo;
		$data["consume_mch_id"] = $consume_mch_id;
		return$this->post(self::URL_SENDREDPACK, $data, true);
	}

	/**
	 * 发放裂变红包
	 * @param $mch_billno string 商户订单号
	 * @param $send_name string 商户名称
	 * @param $re_openid string 用户openid
	 * @param $total_amount int 付款金额 单位分
	 * @param $total_num int 红包发放总人数
	 * @param $wishing string 红包祝福语
	 * @param $act_name string 活动名称
	 * @param $remark string 备注
	 * @param $scene_id string 场景id,发放红包使用场景，红包金额大于200时必传 PRODUCT_1:商品促销 PRODUCT_2:抽奖 PRODUCT_3:虚拟物品兑奖 PRODUCT_4:企业内部福利 PRODUCT_5:渠道分润 PRODUCT_6:保险回馈 PRODUCT_7:彩票派奖 PRODUCT_8:税务刮奖
	 * @param $riskinfo string 活动信息
	 * @param $consume_mch_id string 资金授权商户号
	 * @return array
	 * @throws Exception
	 */
	public function sendGroupRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark,$scene_id = '',$riskinfo = '',$consume_mch_id = ''){
		$data = array();
		$data["wxappid"] = $this->config["app_id"];//NOTE: WXappid
		$data["mch_billno"] = $mch_billno;
		$data["send_name"] = $send_name;
		$data["re_openid"] = $re_openid;
		$data["total_amount"] = $total_amount;
		if($total_amount > 20000 && trim($scene_id)=='') throw new Exception("scene_id is required when total_amount beyond 20000(200rmb)");
		$data["total_num"] = $total_num;
		$data["amt_type"] = 'ALL_RAND'; //红包金额设置方式 ALL_RAND—全部随机
		$data["wishing"] = $wishing;
		$data["act_name"] = $act_name;
		$data["remark"] = $remark;
		$data["scene_id"] = $scene_id;
		$data["riskinfo"] = $riskinfo;
		$data["consume_mch_id"] = $consume_mch_id;
		return $this->post(self::URL_SENDGROUPREDPACK, $data, true);
	}

	/**
	 * 查询红包记录
	 * @param $mch_billno string 商户订单号
	 * @return array
	 * @throws Exception
	 */
	public function getHbInfo($mch_billno){
		$data = array();
		$data["mch_billno"] = $mch_billno;
		$data["appid"] = $this->config["app_id"];
		$data["bill_type"] = 'MCHT'; //MCHT:通过商户订单号获取红包信息。
		return $this->post(self::URL_GETHBINFO, $data, true);
	}

}