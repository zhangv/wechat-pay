<?php

class WechatPay {
	const TRADETYPE_JSAPI = 'JSAPI',TRADETYPE_NATIVE = 'NATIVE',TRADETYPE_APP = 'APP';
    /**
     * 微信支付配置数组
     * appid  公众账号
     * mch_id 商户号
     * key    加密key
     */
    private $_config;

    /**
     * 错误信息
     */
    public $error = null;

    const URL_UNIFIEDORDER = "https://api.mch.weixin.qq.com/pay/unifiedorder",
	    URL_ORDERQUERY = "https://api.mch.weixin.qq.com/pay/orderquery",
		URL_CLOSEORDER = 'https://api.mch.weixin.qq.com/pay/closeorder',
		URL_REFUND = 'https://api.mch.weixin.qq.com/secapi/pay/refund',
		URL_REFUNDQUERY = 'https://api.mch.weixin.qq.com/pay/refundquery',
		URL_DOWNLOADBILL = 'https://api.mch.weixin.qq.com/pay/downloadbill',
		URL_REPORT = 'https://api.mch.weixin.qq.com/payitil/report',
		URL_SHORTURL = 'https://api.mch.weixin.qq.com/tools/shorturl',
		URL_MICROPAY = 'https://api.mch.weixin.qq.com/pay/micropay';

    /**
     * @param $config 微信支付配置数组
     */
    public function __construct($config) {
        $this->_config = $config;
    }

	/**
	 * JSAPI获取prepay_id
	 * @param $body
	 * @param $out_trade_no
	 * @param $total_fee
	 * @param $notify_url
	 * @param $openid
	 * @return null
	 */
	public function getPrepayId($body,$out_trade_no,$total_fee,$notify_url,$openid) {
        $data = array();
        $data["nonce_str"]    = $this->get_nonce_string();
        $data["body"]         = $body;
        $data["out_trade_no"] = $out_trade_no;
        $data["total_fee"]    = $total_fee;
        $data["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
        $data["notify_url"]   = $notify_url;
        $data["trade_type"]   = self::TRADETYPE_JSAPI;
	    $data["openid"]   = $openid;
	    $result = $this->unifiedOrder($data);
		if ($result["return_code"] == "SUCCESS") {
            return $result["prepay_id"];
        } else {
            $this->error = $result["return_msg"];
            return null;
        }
    }

	/**
	 * 扫码支付(模式二)获取支付二维码
	 * @param $body
	 * @param $out_trade_no
	 * @param $total_fee
	 * @param $notify_url
	 * @param $product_id
	 * @return null
	 */
	public function getCodeUrl($body,$out_trade_no,$total_fee,$notify_url,$product_id){
		$data = array();
		$data["nonce_str"]    = $this->get_nonce_string();
		$data["body"]         = $body;
		$data["out_trade_no"] = $out_trade_no;
		$data["total_fee"]    = $total_fee;
		$data["spbill_create_ip"] = $_SERVER["SERVER_ADDR"];
		$data["notify_url"]   = $notify_url;
		$data["trade_type"]   = self::TRADETYPE_NATIVE;
		$data["product_id"]   = $product_id;
		$result = $this->unifiedOrder($data);
		if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
			return $result["code_url"];
		} else {
			$this->error = $result["return_msg"];
			return null;
		}
	}

	/**
	 * 统一下单接口
	 */
	public function unifiedOrder($params) {
		$data = [];
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
	    $data["device_info"] = isset($params['device_info'])?$params['device_info']:null;
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
		$data["notify_url"] = $params['notify_url'];
		$data["trade_type"] = $params['trade_type'];
		$data["product_id"] = isset($params['product_id'])?$params['product_id']:null;//required when trade_type = NATIVE
		$data["openid"] = isset($params['openid'])?$params['openid']:null;//required when trade_type = JSAPI
		$result = $this->post(self::URL_UNIFIEDORDER, $data);
		return $result;
	}

	/**
	 * 查询订单
	 * @param $transaction_id
	 * @param $out_trade_no
	 * @return array
	 */
	public function orderQuery($transaction_id,$out_trade_no){
		$data = [];
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
	 * @param $out_trade_no
	 * @return array
	 */
	public function closeOrder($out_trade_no){
		$data = [];
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
		$data["out_trade_no"] = $out_trade_no;
		$data["nonce_str"] = $this->get_nonce_string();
		$result = $this->post(self::URL_CLOSEORDER, $data);
		return $result;
	}

	/**
	 * 下载对账单
	 * @param $bill_date 下载对账单的日期，格式：20140603
	 * @param $bill_type 类型
	 * @return array
	 */
	public function downloadBill($bill_date,$bill_type = 'ALL'){
		$data = [];
		$data["appid"] = $this->_config["appid"];
		$data["mch_id"] = $this->_config["mch_id"];
		$data["bill_date"] = $bill_date;
		$data["bill_type"] = $bill_type;
		$data["nonce_str"] = $this->get_nonce_string();
		$result = $this->post(self::URL_DOWNLOADBILL, $data);
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


	private function array2xml($array) {
        $xml = "<xml>" . PHP_EOL;
        foreach ($array as $k => $v) {
            $xml .= "<$k><![CDATA[$v]]></$k>" . PHP_EOL;
        }
        $xml .= "</xml>";
        return $xml;
    }

	private function xml2array($xml) {
        $array = array();
        foreach ((array) simplexml_load_string($xml) as $k => $v) {
            $array[$k] = (string) $v;
        }

        return $array;
    }

    private function post($url, $data) {
        $data["sign"] = $this->sign($data);
		$xml = $this->array2xml($data);
	    $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
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
            if ($v) {
                $string1 .= "$k=$v&";
            }
        }
	    $stringSignTemp = $string1 . "key=" . $this->_config["key"];
	    $sign = strtoupper(md5($stringSignTemp));
	    return $sign;
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

    private function get_nonce_string() {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"),0,32);
    }

}
