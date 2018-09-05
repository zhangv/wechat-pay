<?php
/**
 * WechatPay
 *
 * @license MIT
 * @author zhangv
 */
namespace zhangv\wechat\pay;

use \Exception;
use zhangv\wechat\pay\util\HttpClient;
use zhangv\wechat\pay\util\WechatOAuth;
use zhangv\wechat\pay\cache\CacheProvider;
use zhangv\wechat\pay\cache\JsonFileCacheProvider;

/**
 * Class WechatPay
 * @package zhangv\wechat
 * @author zhangv
 * @license MIT
 *
 * @method static service\App       App(array $config)
 * @method static service\Jsapi     Jsapi(array $config)
 * @method static service\Micro     Micro(array $config)
 * @method static service\Mweb      Mweb(array $config)
 * @method static service\Native    Native(array $config)
 * @method static service\Weapp     Weapp(array $config)
 * @method static service\Mchpay    Mchpay(array $config)
 * @method static service\Redpack   Redpack(array $config)
 * @method static service\Coupon    Coupon(array $config)
 */
class WechatPay {
	const TRADETYPE_JSAPI = 'JSAPI',TRADETYPE_NATIVE = 'NATIVE',TRADETYPE_APP = 'APP',TRADETYPE_MWEB = 'MWEB';
	const SIGNTYPE_MD5 = 'MD5', SIGNTYPE_HMACSHA256 = 'HMAC-SHA256';
	const CHECKNAME_FORCECHECK = 'FORCE_CHECK',CHECKNAME_NOCHECK = 'NO_CHECK';
	const ACCOUNTTYPE_BASIC = 'Basic',ACCOUNTTYPE_OPERATION = 'Operation',ACCOUNTTYPE_FEES = 'Fees';
	const API_ENDPOINT = 'https://api.mch.weixin.qq.com/';
	/** 支付 */
	const URL_UNIFIEDORDER = 'pay/unifiedorder';
	const URL_ORDERQUERY = 'pay/orderquery';
	const URL_CLOSEORDER = 'pay/closeorder';
	const URL_REFUND = 'secapi/pay/refund';
	const URL_REFUNDQUERY = 'pay/refundquery';
	const URL_DOWNLOADBILL = 'pay/downloadbill';
	const URL_DOWNLOAD_FUND_FLOW = 'pay/downloadfundflow';
	const URL_REPORT = 'payitil/report';
	const URL_SHORTURL = 'tools/shorturl';
	const URL_MICROPAY = 'pay/micropay';
	const URL_BATCHQUERYCOMMENT = 'billcommentsp/batchquerycomment';
	const URL_REVERSE = 'secapi/pay/reverse';
	const URL_AUTHCODETOOPENID = 'tools/authcodetoopenid';
	/** 红包 */
	const URL_GETHBINFO = 'mmpaymkttransfers/gethbinfo';
	const URL_SENDREDPACK = 'mmpaymkttransfers/sendredpack';
	const URL_SENDGROUPREDPACK = 'mmpaymkttransfers/sendgroupredpack';
	/** 企业付款 */
	const URL_TRANSFER_WALLET = 'mmpaymkttransfers/promotion/transfers';
	const URL_QUERY_TRANSFER_WALLET = 'mmpaymkttransfers/gettransferinfo';
	const URL_TRANSFER_BANKCARD = 'mmpaysptrans/pay_bank';
	const URL_QUERY_TRANSFER_BANKCARD = 'mmpaysptrans/query_bank';
	/** 代金券 */
	const URL_SEND_COUPON = 'mmpaymkttransfers/send_coupon';
	const URL_QUERY_COUPON_STOCK = 'mmpaymkttransfers/query_coupon_stock';
	const URL_QUERY_COUPON_INFO = 'mmpaymkttransfers/querycouponsinfo';
	/** Sandbox获取测试公钥 */
	const URL_GETPUBLICKEY = 'https://fraud.mch.weixin.qq.com/risk/getpublickey';
	public static $BANKCODE = [
		'工商银行' => '1002', '农业银行' => '1005', '中国银行' => '1026', '建设银行' => '1003', '招商银行' => '1001',
		'邮储银行' => '1066', '交通银行' => '1020', '浦发银行' => '1004', '民生银行' => '1006', '兴业银行' => '1009',
		'平安银行' => '1010', '中信银行' => '1021', '华夏银行' => '1025', '广发银行' => '1027', '光大银行' => '1022',
		'北京银行' => '1032', '宁波银行' => '1056',
	];

	public $getSignKeyUrl = "sandboxnew/pay/getsignkey";
	public $sandbox = false;

	/** @var string */
	public $returnCode;
	/** @var string */
	public $returnMsg;
	/** @var string */
	public $resultCode;
	/** @var string */
	public $errCode;
	/** @var string */
	public $errCodeDes;
	/** @var string */
	public $requestXML = null;
	/** @var string */
	public $responseXML = null;
	/** @var array */
	public $requestArray = null;
	/** @var array */
	public $responseArray = null;
	/** @var array */
	protected $config;
	/** @var HttpClient */
	protected $httpClient = null;
	/** @var WechatOAuth */
	protected $wechatOAuth = null;
	/** @var string */
	public $publicKey = null;
	/** @var CacheProvider */
	public $cacheProvider = null;

	/**
	 * @param $config array 配置
	 */
	public function __construct(array $config) {
		$this->config = $config;
		$this->httpClient = new HttpClient(5);
		$this->cacheProvider = new JsonFileCacheProvider();
	}

	/**
	 * @param string $name
	 * @param string $config
	 * @return mixed
	 */
	private static function load($name, $config) {
		$service = __NAMESPACE__ . "\\service\\{$name}";
		return new $service($config);
	}

	/**
	 * @param string $name
	 * @param array  $config
	 *
	 * @return mixed
	 */
	public static function __callStatic($name, $config) {
		return self::load($name, ...$config);
	}

	public function setWechatOAuth($wechatOAuth){
		$this->wechatOAuth = $wechatOAuth;
	}

	public function getWechatOAuth(){
		if(!$this->wechatOAuth){
			$this->wechatOAuth = new WechatOAuth($this->config['app_id'],$this->config['app_secret']);
		}
		return $this->wechatOAuth;
	}

	public function setConfig($config){
		$this->config = $config;
	}

	public function getConfig(){
		return $this->config;
	}

	public function setHttpClient($httpClient){
		$this->httpClient = $httpClient;
	}

	public function setCacheProvider($cacheProvider){
		$this->cacheProvider = $cacheProvider;
	}

	public function getCacheProvider(){
		return $this->cacheProvider;
	}

	/**
	 * 统一下单接口
	 * @param array $params
	 * @throws Exception
	 * @return array
	 */
	public function unifiedOrder($params) {
		$data = array();
		$data["appid"] = $this->config["app_id"];
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
		$data["notify_url"] = $this->config["notify_url"];
		$data["trade_type"] = $params['trade_type'];
		if($params['trade_type'] == WechatPay::TRADETYPE_NATIVE){
			if(!isset($params['product_id'])) throw new Exception('product_id is required when trade_type is NATIVE');
			$data["product_id"] = $params['product_id'];
		}
		if($params['trade_type'] == WechatPay::TRADETYPE_JSAPI){
			if(!isset($params['openid'])) throw new Exception('openid is required when trade_type is JSAPI');
			$data["openid"] = $params['openid'];
		}
		$result = $this->post(self::URL_UNIFIEDORDER, $data);
		return $result;
	}

	/**
	 * 查询订单（根据微信订单号）
	 * @param $transaction_id string 微信订单号
	 * @return array
	 * @throws Exception
	 */
	public function queryOrderByTransactionId($transaction_id){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["transaction_id"] = $transaction_id;
		$result = $this->post(self::URL_ORDERQUERY, $data);
		return $result;
	}

	/**
	 * 查询订单（根据商户订单号）
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 * @throws Exception
	 */
	public function queryOrderByOutTradeNo($out_trade_no){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["out_trade_no"] = $out_trade_no;
		return $this->post(self::URL_ORDERQUERY, $data);
	}

	/**
	 * 查询退款（根据微信订单号）
	 * @param $transaction_id string 微信交易号
	 * @param $offset int 偏移
	 * @return array
	 * @throws Exception
	 */
	public function queryRefundByTransactionId($transaction_id,$offset = 0){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["transaction_id"] = $transaction_id;
		$data["offset"] = $offset;
		return $this->post(self::URL_REFUNDQUERY, $data);
	}

	/**
	 * 查询退款（根据商户订单号）
	 * @param $out_trade_no string 商户交易号
	 * @param $offset int 偏移
	 * @return array
	 * @throws Exception
	 */
	public function queryRefundByOutTradeNo($out_trade_no,$offset = 0){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["out_trade_no"] = $out_trade_no;
		$data["offset"] = $offset;
		return $this->post(self::URL_REFUNDQUERY, $data);
	}

	/**
	 * 查询退款（根据微信退款单号）
	 * @param $refund_id string 微信退款单号
	 * @param $offset int 偏移
	 * @return array
	 * @throws Exception
	 */
	public function queryRefundByRefundId($refund_id,$offset = 0){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["refund_id"] = $refund_id;
		$data["offset"] = $offset;
		return $this->post(self::URL_REFUNDQUERY, $data);
	}

	/**
	 * 查询退款（根据商户退款单号）
	 * @param $out_refund_no string 商户退款单号
	 * @param $offset int 偏移
	 * @return array
	 * @throws Exception
	 */
	public function queryRefundByOutRefundNo($out_refund_no,$offset = 0){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["out_refund_no"] = $out_refund_no;
		$data["offset"] = $offset;
		return $this->post(self::URL_REFUNDQUERY, $data);
	}

	/**
	 * 关闭订单
	 * @param $out_trade_no string 商户订单号
	 * @return array
	 * @throws Exception
	 */
	public function closeOrder($out_trade_no){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["out_trade_no"] = $out_trade_no;
		return $this->post(self::URL_CLOSEORDER, $data,false);
	}

	/**
	 * 退款 - 使用商户订单号
	 * @param $out_trade_no string 商户订单号
	 * @param $out_refund_no string 商户退款单号
	 * @param $total_fee int 总金额（单位：分）
	 * @param $refund_fee int 退款金额（单位：分）
	 * @param $ext array 扩展数组
	 * @return array
	 * @throws Exception
	 */
	public function refundByOutTradeNo($out_trade_no,$out_refund_no,$total_fee,$refund_fee,$ext = array()){
		$data = ($ext && is_array($ext))?$ext:array();
		$data["appid"] = $this->config["app_id"];
		$data["out_trade_no"] = $out_trade_no;
		$data["out_refund_no"] = $out_refund_no;
		$data["total_fee"] = $total_fee;
		$data["refund_fee"] = $refund_fee;
		return $this->post(self::URL_REFUND, $data,true);
	}

	/**
	 * 退款 - 使用微信订单号
	 * @param $transaction_id string 微信订单号
	 * @param $out_refund_no string 商户退款单号
	 * @param $total_fee int 总金额（单位：分）
	 * @param $refund_fee int 退款金额（单位：分）
	 * @param $ext array 扩展数组
	 * @return array
	 * @throws Exception
	 */
	public function refundByTransactionId($transaction_id,$out_refund_no,$total_fee,$refund_fee,$ext = array()){
		$data = ($ext && is_array($ext))?$ext:array();
		$data["appid"] = $this->config["app_id"];
		$data["transaction_id"] = $transaction_id;
		$data["out_refund_no"] = $out_refund_no;
		$data["total_fee"] = $total_fee;
		$data["refund_fee"] = $refund_fee;
		return $this->post(self::URL_REFUND, $data,true);
	}

	/**
	 * 下载对账单
	 * @param $bill_date string 下载对账单的日期，格式：20140603
	 * @param $bill_type string 类型 ALL|SUCCESS
	 * @return array
	 * @throws Exception
	 */
	public function downloadBill($bill_date,$bill_type = 'ALL'){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["bill_date"] = $bill_date;
		$data["bill_type"] = $bill_type;
		return $this->post(self::URL_DOWNLOADBILL, $data);
	}

	/**
	 * 下载资金账单
	 * @param $bill_date string 资金账单日期，格式：20140603
	 * @param $account_type string 资金账户类型 Basic|Operation|Fees
	 * @param $tar_type string 压缩账单
	 * @return array
	 * @throws Exception
	 */
	public function downloadFundFlow($bill_date,$account_type = self::ACCOUNTTYPE_BASIC,$tar_type = 'GZIP'){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["bill_date"] = $bill_date;
		$data["account_type"] = $account_type;
		$data["tar_type"] = $tar_type;
		return $this->post(self::URL_DOWNLOAD_FUND_FLOW, $data);
	}

	/**
	 * 拉取订单评价数据
	 * @param string $begin_time 开始时间,格式为yyyyMMddHHmmss
	 * @param string $end_time 结束时间,格式为yyyyMMddHHmmss
	 * @param int $offset 偏移
	 * @param int $limit 条数
	 * @return array
	 * @throws Exception
	 */
	public function batchQueryComment($begin_time,$end_time,$offset = 0,$limit = 200){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["begin_time"] = $begin_time;
		$data["end_time"] = $end_time;
		$data["offset"] = $offset;
		$data["limit"] = $limit;
		$data["sign"] = $this->sign($data,WechatPay::SIGNTYPE_HMACSHA256);
		return $this->post(self::URL_BATCHQUERYCOMMENT, $data, true); //cert is required
	}

	/**
	 * 支付结果通知处理
	 * @param $notify_data array|string 通知数据
	 * @param $callback callable 回调
	 * @return null
	 * @throws Exception
	 */
	public function onPaidNotify($notify_data,callable $callback = null){
		if(!is_array($notify_data)) $notify_data = $this->xml2array($notify_data);
		if(!$this->validateSign($notify_data)) throw new Exception('Invalid paid notify data');
		if($callback && is_callable($callback)){
			return call_user_func_array( $callback , [$notify_data] );
		}
		return null;
	}

	/**
	 * 退款结果通知处理
	 * @param string|array $notify_data 通知数据(XML/array)
	 * @param callable $callback 回调
	 * @return mixed
	 * @throws Exception
	 */
	public function onRefundedNotify($notify_data,callable $callback = null){
		if(!is_array($notify_data)) $notify_data = $this->xml2array($notify_data);
		if(!$this->validateSign($notify_data)) throw new Exception('Invalid refund notify data');
		if($callback && is_callable($callback)){
			return call_user_func_array( $callback ,[$notify_data] );
		}
		return null;
	}

	/**
	 * 验证数据签名
	 * @param $data array 数据数组
	 * @return boolean 数据校验结果
	 * @throws Exception
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
	 * @param array $data
	 * @param string $return_code 返回状态码 SUCCESS/FAIL
	 * @param string $return_msg  返回信息
	 * @param bool $print
	 * @return string
	 */
	public function responseNotify($print = true,$data = [],$return_code="SUCCESS", $return_msg= 'OK') {
		$data["return_code"] = $return_code;
		if ($return_msg) {
			$data["return_msg"] = $return_msg;
		}
		$xml = $this->array2xml($data);
		if($print === true) print $xml;
		else return $xml;
	}

	/**
	 * 交易保障
	 * @param string $interface_url
	 * @param string $execution_time
	 * @param string $return_code
	 * @param string $result_code
	 * @param string $user_ip
	 * @param string $out_trade_no
	 * @param string $time
	 * @param string $device_info
	 * @param string $return_msg
	 * @param string $err_code
	 * @param string $err_code_des
	 * @return array
	 * @throws Exception
	 */
	public function report($interface_url,$execution_time,$return_code,$result_code,$user_ip,$out_trade_no = null,$time = null,$device_info = null,
	                       $return_msg = null,$err_code = null,$err_code_des = null){
		$data = array();
		$data["appid"] = $this->config["app_id"];
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
		return $this->post(self::URL_REPORT, $data, false);
	}

	/**
	 * 转换短链接
	 * @param $longurl
	 * @return string
	 * @throws Exception
	 */
	public function shortUrl($longurl){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["long_url"] = $longurl;
		$result = $this->post(self::URL_SHORTURL,$data,false);
		return $result['short_url'];
	}

	/**
	 * sandbox环境获取验签秘钥
	 * @return array
	 * @throws Exception
	 */
	public function getSignKey(){
		$data = array();
		$data["mch_id"] = $this->config["mch_id"];
		$result = $this->post($this->getSignKeyUrl,$data,false);
		return $result['sandbox_signkey'];
	}

	/**
	 * 获取JSAPI所需要的页面参数
	 * @param string $url
	 * @param string $ticket
	 * @return array
	 */
	public function getSignPackage($url, $ticket = null){
		if(!$ticket) $ticket = $this->getTicket();
		$timestamp = time();
		$nonceStr = $this->getNonceStr();
		$rawString = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		$signature = sha1($rawString);

		$signPackage = array(
			"appId" => $this->config['app_id'],
			"nonceStr" => $nonceStr,
			"timestamp" => $timestamp,
			"url" => $url,
			"signature" => $signature,
			"rawString" => $rawString
		);
		return $signPackage;
	}

	/**
	 * 获取JSAPI Ticket
	 * @param boolean $cache
	 * @return string
	 */
	public function getTicket($cache = true){
		$ticket = null;
		$cacheKey = 'jsapi_ticket';
		if($cache === true){
			$data = $this->cacheProvider->get($cacheKey);
			if ($data && $data->expires_at > time()) {
				$ticket = $data->ticket;
			}
		}
		if(!$ticket){
			$data = $this->getWechatOAuth()->getTicket();
			if($cache === true){
				$this->cacheProvider->set($cacheKey,$data,time() + $data->expires_in);
			}
			$ticket = $data->ticket;
		}
		return $ticket;
	}

	protected function post($url, $data,$cert = true) {
		if(!isset($data['mch_id']) && !isset($data['mchid'])) $data["mch_id"] = $this->config["mch_id"];
		if(!isset($data['nonce_str'])) $data["nonce_str"] = $this->getNonceStr();
		if(!isset($data['sign'])) $data['sign'] = $this->sign($data);
		$this->requestXML = $this->responseXML = null;
		$this->requestArray = $this->responseArray = null;

		$this->requestArray = $data;
		$this->requestXML = $this->array2xml($data);
		$opts = [
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10
		];
		if($cert == true){
			$opts[CURLOPT_SSLCERTTYPE] = 'PEM';
			$opts[CURLOPT_SSLCERT] = $this->config['ssl_cert_path'];
			$opts[CURLOPT_SSLKEYTYPE] = 'PEM';
			$opts[CURLOPT_SSLKEY] = $this->config['ssl_key_path'];
		}
		$processResponse = true;
		if(in_array($url,[self::URL_DOWNLOADBILL,self::URL_DOWNLOAD_FUND_FLOW,self::URL_BATCHQUERYCOMMENT])){
			$processResponse = false;
		}
		if($this->sandbox === true) $url = "sandboxnew/{$url}";

		$content = $this->httpClient->post(self::API_ENDPOINT . $url,$this->requestXML,[],$opts);
		if(!$content) throw new Exception("Empty response with {$this->requestXML}");

		$this->responseXML = $content;
		if($processResponse)
			return $this->processResponseXML($this->responseXML);
		else return $this->responseXML;
	}

	/**
	 * @param $responseXML
	 * @return array
	 * @throws Exception
	 */
	private function processResponseXML($responseXML){
		$result = $this->xml2array($responseXML);
		$this->responseArray = $result;
		if(empty($result['return_code'])){
			throw new Exception("No return code presents in {$this->responseXML}");
		}
		$this->returnCode = $result["return_code"];
		$this->returnMsg = isset($result['return_msg'])?$result['return_msg']:'';

		if ($this->returnCode == "SUCCESS") {
			if(isset($result['result_code']) && $result['result_code'] == "FAIL"){
				$this->resultCode = $result['result_code'];
				$this->errCode = $result['err_code'];
				$this->errCodeDes = $result['err_code_des'];
				throw new Exception("[$this->errCode]$this->errCodeDes");
			}else{
				return $result;
			}
		} else if($this->returnCode == 'FAIL'){
			throw new Exception($this->returnMsg);
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
		$stringSignTemp = $string1 . "key=" . $this->config["api_key"];
		if($sign_type == WechatPay::SIGNTYPE_MD5){
			$sign = strtoupper(md5($stringSignTemp));
		}elseif($sign_type == WechatPay::SIGNTYPE_HMACSHA256){
			$sign = strtoupper(hash_hmac('sha256',$stringSignTemp,$this->config["api_key"]));
		}else throw new Exception("Not supported sign type - $sign_type");
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
		$array = [];
		$tmp = (array) simplexml_load_string($xml);
		foreach ( $tmp as $k => $v) {
			$array[$k] = (string) $v;
		}
		return $array;
	}

	protected function getNonceStr() {
		return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"),0,32);
	}

}