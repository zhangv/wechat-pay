<?php
/**
 * TODO: MWEB
 * @author zhangv
 */
class WechatPay {
	const TRADETYPE_JSAPI = 'JSAPI',TRADETYPE_NATIVE = 'NATIVE',TRADETYPE_APP = 'APP',TRADETYPE_MWEB = 'MWEB';
	const SIGNTYPE_MD5 = 'MD5', SIGNTYPE_HMACSHA256 = 'HMAC-SHA256';

	const URL_UNIFIEDORDER = "https://api.mch.weixin.qq.com/pay/unifiedorder";
	const URL_ORDERQUERY = "https://api.mch.weixin.qq.com/pay/orderquery";
	const URL_CLOSEORDER = 'https://api.mch.weixin.qq.com/pay/closeorder';
	const URL_REFUND = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
	const URL_REFUNDQUERY = 'https://api.mch.weixin.qq.com/pay/refundquery';
	const URL_DOWNLOADBILL = 'https://api.mch.weixin.qq.com/pay/downloadbill';
	const URL_REPORT = 'https://api.mch.weixin.qq.com/payitil/report';
	const URL_SHORTURL = 'https://api.mch.weixin.qq.com/tools/shorturl';
	const URL_MICROPAY = 'https://api.mch.weixin.qq.com/pay/micropay';
	const URL_SENDREDPACK = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
	const URL_SENDGROUPREDPACK = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';
	const URL_GETHBINFO = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';
	const URL_BATCHQUERYCOMMENT = 'https://api.mch.weixin.qq.com/billcommentsp/batchquerycomment';
	const URL_REVERSE = 'https://api.mch.weixin.qq.com/secapi/pay/reverse';
	const URL_AUTHCODETOOPENID = 'https://api.mch.weixin.qq.com/tools/authcodetoopenid';

	/**
	 * 错误信息
	 */
	public $error = null;
	/**
	 * 错误信息XML
	 */
	public $errorXML = null;
	public $returnCode,$returnMsg,$resultCode,$errCode,$errCodeDes;

	/**
	 * 微信支付配置数组
	 * appid        公众账号appid
	 * mch_id       商户号
	 * apikey       支付密钥
	 * appsecret    公众号appsecret
	 * sslcertPath  证书路径(apiclient_cert.pem)
	 * sslkeyPath   密钥路径(apiclient_key.pem)
	 * notify_url   通知url
	 */
	private $_config;

	private $httpClient = null;

	/**
	 * @param $config array 微信支付配置数组
	 */
	public function __construct($config) {
		$this->_config = $config;
	}

	public function setHttpClient($httpClient){
		$this->httpClient = $httpClient;
	}

	/**
	 * 获取JSAPI的prepay_id
	 * @param $body string 内容
	 * @param $out_trade_no string 商户订单号
	 * @param $total_fee int 总金额
	 * @param $openid string openid
	 * @param $ext array
	 * @param $trade_type string 交易类型
	 * @return string
	 */
	public function getPrepayId($body,$out_trade_no,$total_fee,$openid,$ext = null, $trade_type = WechatPay::TRADETYPE_JSAPI) {
		$data = ($ext && is_array($ext))?$ext:array();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:'';
		$data["notify_url"]   = $this->_config["notify_url"];
		$data["trade_type"]   = $trade_type;
		$data["openid"]   = $openid;
		$result = $this->unifiedOrder($data);
		return $result["prepay_id"];
	}

	/**
	 * 获取APP的prepay_id(注意这里的appid是从开放平台申请的)
	 * ref:https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1
	 * @param $body string 内容
	 * @param $out_trade_no string 商户订单号
	 * @param $total_fee int 总金额
	 * @param $spbill_create_ip string openid
	 * @param $ext array
	 * @return string
	 */
	public function getPrepayIdAPP($body,$out_trade_no,$total_fee,$spbill_create_ip,$ext = null) {
		$data = ($ext && is_array($ext))?$ext:array();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $spbill_create_ip;
		$data["notify_url"]   = $this->_config["notify_url"];
		$data["trade_type"]   = WechatPay::TRADETYPE_APP;
		$result = $this->unifiedOrder($data);
		return $result["prepay_id"];
	}

	/**
	 * 扫码支付(模式二)获取支付二维码
	 * @param $body
	 * @param $out_trade_no
	 * @param $total_fee
	 * @param $product_id
	 * @return null
	 */
	public function getCodeUrl($body,$out_trade_no,$total_fee,$product_id){
		$data = array();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:'';
		$data["notify_url"]   = $this->_config["notify_url"];
		$data["trade_type"]   = self::TRADETYPE_NATIVE;
		$data["product_id"]   = $product_id;
		$result = $this->unifiedOrder($data);
		return $result["code_url"];
	}

	/**
	 * H5支付获取支付跳转链接
	 * ref:https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_20&index=1
	 * @param $body string 商品描述
	 * @param $out_trade_no string 商户订单号
	 * @param $total_fee int 总金额(分)
	 * @return string
	 * @throws Exception
	 */
	public function getMwebUrl($body,$out_trade_no,$total_fee){
		$data = array();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:'';
		$data["notify_url"]   = $this->_config["notify_url"];
		$data["trade_type"]   = self::TRADETYPE_MWEB;
		if(!isset($this->_config['h5_scene_info'])) throw new Exception('h5_scene_info should be configured');
		$data["scene_info"]   = json_encode($this->_config['h5_scene_info']);
		$result = $this->unifiedOrder($data);
		return $result["mweb_url"];
	}

	/**
	 * 统一下单接口
	 */
	private function unifiedOrder($params) {
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["device_info"] = (isset($params['device_info'])&&trim($params['device_info'])!='')?$params['device_info']:null;
		$data["body"] = $params['body'];
		$data["detail"] = isset($params['detail'])?$params['detail']:null;//optional
		$data["attach"] = isset($params['attach'])?$params['attach']:null;//optional
		$data["out_trade_no"] = isset($params['out_trade_no'])?$params['out_trade_no']:null;
		$data["fee_type"] = isset($params['fee_type'])?$params['fee_type']:'CNY';
		$data["total_fee"]    = $params['total_fee'];
		$data["spbill_create_ip"] = $params['spbill_create_ip'];
		$data["time_start"] = isset($params['time_start'])?$params['time_start']:null;//optional
		$data["time_expire"] = isset($params['time_expire'])?$params['time_expire']:null;//optional
		$data["goods_tag"] = isset($params['goods_tag'])?$params['goods_tag']:null;
		$data["notify_url"] = $this->_config["notify_url"];
		$data["trade_type"] = $params['trade_type'];
		$data["product_id"] = isset($params['product_id'])?$params['product_id']:null;//required when trade_type = NATIVE
		$data["openid"] = isset($params['openid'])?$params['openid']:null;//required when trade_type = JSAPI
		$result = $this->post(self::URL_UNIFIEDORDER, $data);
		return $result;
	}

	/**
	 * 查询订单（根据微信订单号）
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_2
	 * @param $transaction_id string 微信订单号
	 * @return array
	 */
	public function queryOrderByTransactionId($transaction_id){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["transaction_id"] = $transaction_id;
		$result = $this->post(self::URL_ORDERQUERY, $data);
		return $result;
	}

	/**
	 * 查询订单（根据商户订单号）
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_2
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 */
	public function queryOrderByOutTradeNo($out_trade_no){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["out_trade_no"] = $out_trade_no;
		$result = $this->post(self::URL_ORDERQUERY, $data);
		return $result;
	}

	/**
	 * 查询退款（根据微信订单号）
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_5
	 * @param $transaction_id string 微信交易号
	 * @param $offset int 偏移
	 * @return array
	 */
	public function queryRefundByTransactionId($transaction_id,$offset = 0){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["transaction_id"] = $transaction_id;
		$result = $this->post(self::URL_REFUNDQUERY, $data);
		return $result;
	}

	/**
	 * 查询退款（根据商户订单号）
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_5
	 * @param $out_trade_no string 商户交易号
	 * @param $offset int 偏移
	 * @return array
	 */
	public function queryRefundByOutTradeNo($out_trade_no,$offset = 0){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["out_trade_no"] = $out_trade_no;
		$result = $this->post(self::URL_REFUNDQUERY, $data);
		return $result;
	}

	/**
	 * 查询退款（根据微信退款单号）
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_5
	 * @param $refund_id string 微信退款单号
	 * @param $offset int 偏移
	 * @return array
	 */
	public function queryRefundByRefundId($refund_id,$offset = 0){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["refund_id"] = $refund_id;
		$result = $this->post(self::URL_REFUNDQUERY, $data);
		return $result;
	}

	/**
	 * 查询退款（根据商户退款单号）
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_5
	 * @param $out_refund_no string 商户退款单号
	 * @param $offset int 偏移
	 * @return array
	 */
	public function queryRefundByOutRefundNo($out_refund_no,$offset = 0){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["out_refund_no"] = $out_refund_no;
		$result = $this->post(self::URL_REFUNDQUERY, $data);
		return $result;
	}

	/**
	 * 关闭订单
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_3
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 */
	public function closeOrder($out_trade_no){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["out_trade_no"] = $out_trade_no;
		$result = $this->post(self::URL_CLOSEORDER, $data,false);
		return $result;
	}

	/**
	 * 申请退款 - 使用商户订单号
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_4
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4
	 * @param $out_trade_no string 商户订单号
	 * @param $out_refund_no string 商户退款单号
	 * @param $total_fee int 总金额（单位：分）
	 * @param $refund_fee int 退款金额（单位：分）
	 * @param $ext array 扩展数组
	 * @return array
	 */
	public function refundByOutTradeNo($out_trade_no,$out_refund_no,$total_fee,$refund_fee,$ext = array()){
		$data = ($ext && is_array($ext))?$ext:array();
		$data["appid"] = $this->_config["appid"];
		$data["out_trade_no"] = $out_trade_no;
		$data["out_refund_no"] = $out_refund_no;
		$data["total_fee"] = $total_fee;
		$data["refund_fee"] = $refund_fee;
		$result = $this->post(self::URL_REFUND, $data,true);
		return $result;
	}

	/**
	 * 申请退款 - 使用微信订单号
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_4
	 * ref: https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_4
	 * @param $transaction_id string 微信订单号
	 * @param $out_refund_no string 商户退款单号
	 * @param $total_fee int 总金额（单位：分）
	 * @param $refund_fee int 退款金额（单位：分）
	 * @param $ext array 扩展数组
	 * @return array
	 */
	public function refundByTransactionId($transaction_id,$out_refund_no,$total_fee,$refund_fee,$ext = array()){
		$data = ($ext && is_array($ext))?$ext:array();
		$data["appid"] = $this->_config["appid"];
		$data["transaction_id"] = $transaction_id;
		$data["out_refund_no"] = $out_refund_no;
		$data["total_fee"] = $total_fee;
		$data["refund_fee"] = $refund_fee;
		$result = $this->post(self::URL_REFUND, $data,true);
		return $result;
	}

	/**
	 * 撤销订单 - 使用商户订单号
	 * https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_11&index=3
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 */
	public function reverseByOutTradeNo($out_trade_no){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["out_trade_no"] = $out_trade_no;
		$result = $this->post(self::URL_REVERSE, $data,true);
		return $result;
	}

	/**
	 * 撤销订单 - 使用微信订单号
	 * https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_11&index=3
	 * @param $transaction_id string 微信订单号
	 * @return array
	 */
	public function reverseByTransactionId($transaction_id){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["transaction_id"] = $transaction_id;
		$result = $this->post(self::URL_REVERSE, $data,true);
		return $result;
	}

	/**
	 * 下载对账单
	 * @param $bill_date string 下载对账单的日期，格式：20140603
	 * @param $bill_type string 类型
	 * @return array
	 */
	public function downloadBill($bill_date,$bill_type = 'ALL'){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["bill_date"] = $bill_date;
		$data["bill_type"] = $bill_type;
		$result = $this->post(self::URL_DOWNLOADBILL, $data);
		return $result;
	}

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
	 * @ref https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_4&index=3
	 */
	public function sendRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark,$scene_id = '',$riskinfo = '',$consume_mch_id = ''){
		$data = array();
		$data["wxappid"] = $this->_config["appid"];
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
		$result = $this->post(self::URL_SENDREDPACK, $data, true); //cert is required
		return $result;
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
	 * @ref https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_5&index=4
	 */
	public function sendGroupRedPack($mch_billno,$send_name,$re_openid,$total_amount,$total_num,$wishing,$act_name,$remark,$scene_id = '',$riskinfo = '',$consume_mch_id = ''){
		$data = array();
		$data["wxappid"] = $this->_config["appid"];//NOTE: WXappid
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
		$result = $this->post(self::URL_SENDGROUPREDPACK, $data, true); //cert is required
		return $result;
	}

	/**
	 * 查询红包记录
	 * @param $mch_billno string 商户订单号
	 * @return array
	 * @throws Exception
	 * @ref https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_6&index=5
	 */
	public function getHbInfo($mch_billno){
		$data = array();
		$data["mch_billno"] = $mch_billno;
		$data["appid"] = $this->_config["appid"];
		$data["bill_type"] = 'MCHT'; //MCHT:通过商户订单号获取红包信息。
		$result = $this->post(self::URL_GETHBINFO, $data, true); //cert is required
		return $result;
	}

	/**
	 * 拉取订单评价数据
	 * @param string $begin_time 开始时间,格式为yyyyMMddHHmmss
	 * @param string $end_time 结束时间,格式为yyyyMMddHHmmss
	 * @param int $offset 偏移
	 * @param int $limit 条数
	 * @return array
	 */
	public function batchQueryComment($begin_time,$end_time,$offset = 0,$limit = 200){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["begin_time"] = $begin_time;
		$data["end_time"] = $end_time;
		$data["offset"] = $offset;
		$data["limit"] = $limit;
		$data["sign"] = $this->sign($data,WechatPay::SIGNTYPE_HMACSHA256);
		$result = $this->post(self::URL_BATCHQUERYCOMMENT, $data, true); //cert is required
		return $result;
	}

	/**
	 * 获取JS支付/APP支付使用的第二个参数
	 * @param $prepay_id string 预支付ID
	 * @param $trade_type string 支付类型
	 * @return array
	 */
	public function getPackage($prepay_id, $trade_type = WechatPay::TRADETYPE_JSAPI) {
		$data = array();
		if ($trade_type == WechatPay::TRADETYPE_JSAPI){
			$data["package"]   = "prepay_id=$prepay_id";
			$data["timeStamp"] = time();
			$data["nonceStr"]  = $this->get_nonce_string();
			$data["appId"] = $this->_config["appid"];
			$data["signType"]  = "MD5";
			$data["paySign"]   = $this->sign($data);
		} else if ($trade_type == WechatPay::TRADETYPE_APP){
			$data["package"]   = "Sign=WXPay";
			$data['prepayid'] = $prepay_id;
			$data['partnerid'] = $this->_config["mch_id"];
			$data["timestamp"] = time();
			$data["noncestr"]  = $this->get_nonce_string();
			$data["appid"] = $this->_config["appid"];
			$data["sign"]   = $this->sign($data);
		}
		return $data;
	}

	/**
	 * 提交刷卡支付
	 * @param $body
	 * @param $out_trade_no
	 * @param $total_fee
	 * @param $spbill_create_ip
	 * @param $auth_code
	 * @param array $ext
	 * @return array
	 */
	public function microPay($body,$out_trade_no,$total_fee,$spbill_create_ip,$auth_code,$ext = array()){
		$data = ($ext && is_array($ext))?$ext:array();
		$data["appid"] = $this->_config["appid"];
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $spbill_create_ip;
		$data["auth_code"] = $auth_code;
		$result = $this->post(self::URL_MICROPAY,$data,false);
		return $result;
	}

	/**
	 * later
	 * 获取发送到通知地址的数据(在通知地址内使用)
	 * @return array 结果数组，如果不是微信服务器发送的数据返回null
	 *          appid
	 *          bank_type
	 *          cash_fee
	 *          fee_type
	 *          is_subscribe
	 *          mch_id
	 *          nonce_str
	 *          openid
	 *          out_trade_no    商户订单号
	 *          result_code
	 *          return_code
	 *          sign
	 *          time_end
	 *          total_fee       总金额
	 *          trade_type
	 *          transaction_id  微信支付订单号
	 */
	public function get_back_data() {
		$xml = file_get_contents("php://input");
		$data = $this->xml2array($xml);
		if ($this->validateSign($data)) {
			return $data;
		} else {
			return null;
		}
	}

	/**
	 * 支付结果通知处理
	 * @param $notify_data array/XML 通知数据
	 * @param $callback callable 回调
	 * @return null
	 * @throws Exception
	 */
	public function onPaidNotify($notify_data,callable $callback = null){
		if(!is_array($notify_data)){
			$notify_data = $this->xml2array($notify_data);
		}
		if($this->validateSign($notify_data)){
			if($callback && is_callable($callback)){
				return call_user_func_array( $callback , [$notify_data] );
			}else{
				$this->responseNotify();
			}
		}else{
			throw new Exception('Invalid paid notify data');
		}
	}

	/**
	 * 退款结果通知处理
	 * @param $notify_data array/XML 通知数据
	 * @param $callback callable 回调
	 * @return null
	 * @throws Exception
	 */
	public function onRefundedNotify($notify_data,callable $callback = null){
		if(!is_array($notify_data)){
			$notify_data = $this->xml2array($notify_data);
		}
		if($this->validateSign($notify_data)){
			if($callback && is_callable($callback)){
				return call_user_func_array( $callback , $notify_data );
			}else{
				$this->responseNotify();
			}
		}else{
			throw new Exception('Invalid refunded notify data');
		}
	}

	/**
	 * 验证数据签名
	 * @param $data array 数据数组
	 * @return boolean 数据校验结果
	 */
	public function validateSign($data) {
		if (!isset($data["sign"])) {
			return false;
		}
		$sign = $data["sign"];
		unset($data["sign"]);
		return $this->sign($data) == $sign;
	}

	/**
	 * 响应微信支付后台通知
	 * @param $return_code string 返回状态码 SUCCESS/FAIL
	 * @param $return_msg string 返回信息
	 */
	public function responseNotify($return_code="SUCCESS", $return_msg= 'OK') {
		$data = array();
		$data["return_code"] = $return_code;
		if ($return_msg) {
			$data["return_msg"] = $return_msg;
		}
		$xml = $this->array2xml($data);
		print $xml;
	}

	/**
	 * 交易保障
	 * ref:https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_8&index=8
	 * @param $interface_url
	 * @param $execution_time
	 * @param $return_code
	 * @param $result_code
	 * @param $user_ip
	 * @param null $out_trade_no
	 * @param null $time
	 * @param null $device_info
	 * @param null $return_msg
	 * @param null $err_code
	 * @param null $err_code_des
	 * @return array
	 */
	public function report($interface_url,$execution_time,$return_code,$result_code,$user_ip,$out_trade_no = null,$time = null,$device_info = null,
	                       $return_msg = null,$err_code = null,$err_code_des = null){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["interface_url"] = $interface_url;
		$data["execution_time"] = $execution_time;
		$data["return_code"] = $return_code;
		$data["result_code"] = $result_code;
		$data["user_ip"] = $user_ip;
		if($out_trade_no) $data["out_trade_no"] = $out_trade_no;
		if($time) $data["time"] = $time;
		if($device_info) $data["device_info"] = $device_info;
		if($return_msg) $data["return_msg"] = $return_msg;
		if($err_code) $data["err_code"] = $err_code;
		if($err_code_des) $data["err_code_des"] = $err_code_des;
		$result = $this->post(self::URL_REPORT, $data, false); //cert is NOT required
		return $result;
	}

	/**
	 * 转换短链接
	 * @param $longurl
	 * @return string
	 */
	public function shortUrl($longurl){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["long_url"] = $longurl;
		$result = $this->post(self::URL_SHORTURL,$data,false);
		return $result['short_url'];
	}

	/**
	 * 授权码查询openid
	 * @param $auth_code
	 * @return mixed
	 */
	public function authCodeToOpenId($auth_code){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["auth_code"] = $auth_code;
		$result = $this->post(self::URL_AUTHCODETOOPENID,$data,false);
		return $result['openid'];
	}

	private function post($url, $data,$cert = true) {
		if(!isset($data['mch_id'])) $data["mch_id"] = $this->_config["mch_id"];
		if(!isset($data['nonce_str'])) $data["nonce_str"] = $this->get_nonce_string();
		if(!isset($data['sign'])) $data['sign'] = $this->sign($data);

		$xml = $this->array2xml($data);
		$content = $this->httpClient->post($url,$xml,$this->_config,$cert);
		$result = $this->xml2array($content);
		$this->returnCode = $result["return_code"];
		if ($this->returnCode == "SUCCESS" && $result["result_code"] == "SUCCESS") {
			return $result;
		} else {
			$this->errorXML = $this->array2xml($result);
			if($result["return_code"] == "FAIL"){
				$this->returnMsg = $result['return_msg'];
				throw new Exception($this->returnMsg);
			}else{
				$this->resultCode = $result['result_code'];
				$this->errCode = $result['err_code'];
				$this->errCodeDes = $result['err_code_des'];
				throw new Exception($this->errCodeDes);
			}
		}
	}

	public function sign($data,$sign_type = WechatPay::SIGNTYPE_MD5) {
		ksort($data);
		$string1 = "";
		foreach ($data as $k => $v) {
			if ($v && trim($v)!='') {
				$string1 .= "$k=$v&";
			}
		}
		$stringSignTemp = $string1 . "key=" . $this->_config["apikey"];
		if($sign_type == WechatPay::SIGNTYPE_MD5){
			$sign = strtoupper(md5($stringSignTemp));
		}else{
			$sign = strtoupper(hash_hmac('sha256',$stringSignTemp,$this->_config["apikey"]));
		}
		return $sign;
	}

	private function array2xml($array) {
		$xml = "<xml>" . PHP_EOL;
		foreach ($array as $k => $v) {
			if($v && trim($v)!='')
				$xml .= "<$k><![CDATA[$v]]></$k>" . PHP_EOL;
		}
		$xml .= "</xml>";
		return $xml;
	}

	private function xml2array($xml) {
		$array = array();
		$tmp = null;
		try{
			$tmp = (array) simplexml_load_string($xml);
		}catch(Exception $e){}
		if($tmp && is_array($tmp)){
			foreach ( $tmp as $k => $v) {
				$array[$k] = (string) $v;
			}
		}
		return $array;
	}

	private function get_nonce_string() {
		return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"),0,32);
	}

}

class HttpClient{

	public function post($url, $data,$config,$cert = true) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		if($cert == true){
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $config['sslcertPath']);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $config['sslkeyPath']);
		}
		return curl_exec($ch);
	}

}