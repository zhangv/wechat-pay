<?php
/**
 *
 * @author zhangv
 */
class WechatPay {
	const TRADETYPE_JSAPI = 'JSAPI',TRADETYPE_NATIVE = 'NATIVE',TRADETYPE_APP = 'APP';
	const URL_UNIFIEDORDER = "https://api.mch.weixin.qq.com/pay/unifiedorder",
		URL_ORDERQUERY = "https://api.mch.weixin.qq.com/pay/orderquery",
		URL_CLOSEORDER = 'https://api.mch.weixin.qq.com/pay/closeorder',
		URL_REFUND = 'https://api.mch.weixin.qq.com/secapi/pay/refund',
		URL_REFUNDQUERY = 'https://api.mch.weixin.qq.com/pay/refundquery',
		URL_DOWNLOADBILL = 'https://api.mch.weixin.qq.com/pay/downloadbill',
		URL_REPORT = 'https://api.mch.weixin.qq.com/payitil/report',
		URL_SHORTURL = 'https://api.mch.weixin.qq.com/tools/shorturl',
		URL_MICROPAY = 'https://api.mch.weixin.qq.com/pay/micropay',
		URL_SENDREDPACK = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack',
		URL_SENDGROUPREDPACK = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack',
		URL_GETHBINFO = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';
	/**
	 * 错误信息
	 */
	public $error = null;
	/**
	 * 错误信息XML
	 */
	public $errorXML = null;
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

	/**
	 * @param $config array 微信支付配置数组
	 */
	public function __construct($config) {
		$this->_config = $config;
	}

	/**
	 * JSAPI获取prepay_id
	 * @param $body string 内容
	 * @param $out_trade_no string 商户订单号
	 * @param $total_fee int 总金额
	 * @param $openid string openid
	 * @return string
	 */
	public function getPrepayId($body,$out_trade_no,$total_fee,$openid,$ext = null) {
		$data = ($ext && is_array($ext))?$ext:[];
		$data["nonce_str"]    = $this->get_nonce_string();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
		$data["notify_url"]   = $this->_config["notify_url"];
		$data["trade_type"]   = self::TRADETYPE_JSAPI;
		$data["openid"]   = $openid;
		$result = $this->unifiedOrder($data);
		if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
			return $result["prepay_id"];
		} else {
			$this->error = $result["return_code"] == "SUCCESS" ? $result["err_code_des"] : $result["return_msg"];
			$this->errorXML = $this->array2xml($result);
			return null;
		}
	}

	/**
	 * 统一下单接口
	 */
	private function unifiedOrder($params) {
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
		$data["device_info"] = (isset($params['device_info'])&&trim($params['device_info'])!='')?$params['device_info']:null;
		$data["nonce_str"] = $this->get_nonce_string();
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
	 * 扫码支付(模式二)获取支付二维码
	 * @param $body
	 * @param $out_trade_no
	 * @param $total_fee
	 * @param $product_id
	 * @return null
	 */
	public function getCodeUrl($body,$out_trade_no,$total_fee,$product_id){
		$data = array();
		$data["nonce_str"]    = $this->get_nonce_string();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $_SERVER["SERVER_ADDR"];
		$data["notify_url"]   = $this->_config["notify_url"];;
		$data["trade_type"]   = self::TRADETYPE_NATIVE;
		$data["product_id"]   = $product_id;
		$result = $this->unifiedOrder($data);
		if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
			return $result["code_url"];
		} else {
			$this->error = (isset($result["return_code"]) && $result["return_code"] == "SUCCESS") ? $result["return_msg"]:$result["err_code_des"] ;
			return null;
		}
	}

	/**
	 * 查询订单
	 * @param $transaction_id string 微信交易号
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 */
	public function orderQuery($transaction_id,$out_trade_no){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
		$data["transaction_id"] = $transaction_id;
		$data["out_trade_no"] = $out_trade_no;
		$data["nonce_str"] = $this->get_nonce_string();
		$result = $this->post(self::URL_ORDERQUERY, $data);
		return $result;
	}

	/**
	 * 关闭订单
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 */
	public function closeOrder($out_trade_no){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
		$data["out_trade_no"] = $out_trade_no;
		$data["nonce_str"] = $this->get_nonce_string();
		$result = $this->post(self::URL_CLOSEORDER, $data);
		return $result;
	}

	/**
	 * 申请退款 - 使用商户订单号
	 * @param $out_trade_no string 商户订单号
	 * @param $out_refund_no string 退款单号
	 * @param $total_fee int 总金额（单位：分）
	 * @param $refund_fee int 退款金额（单位：分）
	 * @param $op_user_id string 操作员账号
	 * @return array
	 */
	public function refund($out_trade_no,$out_refund_no,$total_fee,$refund_fee,$op_user_id){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
		$data["nonce_str"] = $this->get_nonce_string();
		$data["out_trade_no"] = $out_trade_no;
		$data["out_refund_no"] = $out_refund_no;
		$data["total_fee"] = $total_fee;
		$data["refund_fee"] = $refund_fee;
		$data["op_user_id"] = $op_user_id;
		$result = $this->post(self::URL_REFUND, $data,true);

		return $result;
	}

	/**
	 * 申请退款 - 使用微信订单号
	 * @param $transaction_id string 微信交易号
	 * @param $out_refund_no string 退款单号
	 * @param $total_fee int 总金额（单位：分）
	 * @param $refund_fee int 退款金额（单位：分）
	 * @param $op_user_id string 操作员账号
	 * @return array
	 */
	public function refundByTransId($transaction_id,$out_refund_no,$total_fee,$refund_fee,$op_user_id){
		$data = array();
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
		$data["nonce_str"] = $this->get_nonce_string();
		$data["transaction_id"] = $transaction_id;
		$data["out_refund_no"] = $out_refund_no;
		$data["total_fee"] = $total_fee;
		$data["refund_fee"] = $refund_fee;
		$data["op_user_id"] = $op_user_id;
		$result = $this->post(self::URL_REFUND, $data,true);
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
		$data["mch_id"] = $this->_config["mch_id"];
		$data["bill_date"] = $bill_date;
		$data["bill_type"] = $bill_type;
		$data["nonce_str"] = $this->get_nonce_string();
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
		$data["mch_id"] = $this->_config["mch_id"];
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
		$data["nonce_str"] = $this->get_nonce_string();
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
		$data["wxappid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
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
		$data["nonce_str"] = $this->get_nonce_string();
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
		$data["mch_id"] = $this->_config["mch_id"];
		$data["appid"] = $this->_config["appid"];
		$data["bill_type"] = 'MCHT'; //MCHT:通过商户订单号获取红包信息。
		$data["nonce_str"] = $this->get_nonce_string();
		$result = $this->post(self::URL_GETHBINFO, $data, true); //cert is required
		return $result;
	}

	/**
	 * 获取js支付使用的第二个参数
	 */
	public function get_package($prepay_id) {
		$data = array();
		$data["appId"] = $this->_config["appid"];
		$data["timeStamp"] = time();
		$data["nonceStr"]  = $this->get_nonce_string();
		$data["package"]   = "prepay_id=$prepay_id";
		$data["signType"]  = "MD5";
		$data["paySign"]   = $this->sign($data);
		return $data;
	}

	/**
	 * 获取发送到通知地址的数据(在通知地址内使用)
	 * @return 结果数组，如果不是微信服务器发送的数据返回null
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
		if ($this->validate($data)) {
			return $data;
		} else {
			return null;
		}
	}

	/**
	 * 验证数据签名
	 * @param $data 数据数组
	 * @return 数据校验结果
	 */
	public function validate($data) {
		if (!isset($data["sign"])) {
			return false;
		}
		$sign = $data["sign"];
		unset($data["sign"]);
		return $this->sign($data) == $sign;
	}

	/**
	 * 响应微信支付后台通知
	 * @param $return_code 返回状态码 SUCCESS/FAIL
	 * @param $return_msg  返回信息
	 */
	public function response_back($return_code="SUCCESS", $return_msg=null) {
		$data = array();
		$data["return_code"] = $return_code;
		if ($return_msg) {
			$data["return_msg"] = $return_msg;
		}
		$xml = $this->array2xml($data);

		print $xml;
	}

	private function post($url, $data,$cert = true) {
		$data["sign"] = $this->sign($data);
		$xml = $this->array2xml($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		if($cert == true){
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $this->_config['sslcertPath']);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $this->_config['sslkeyPath']);
		}
		$content = curl_exec($ch);
		$array = $this->xml2array($content);
		return $array;
	}

	/**
	 * 数据签名
	 * @param $data
	 * @return string
	 */
	private function sign($data) {
		ksort($data);
		$string1 = "";
		foreach ($data as $k => $v) {
			if ($v && trim($v)!='') {
				$string1 .= "$k=$v&";
			}
		}
		$stringSignTemp = $string1 . "key=" . $this->_config["apikey"];
		$sign = strtoupper(md5($stringSignTemp));
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
