<?php
namespace zhangv\wechat\pay\service;
use \zhangv\wechat\pay\WechatPay;
use \Exception;

/**
 * 企业付款
 * @license MIT
 * @zhangv
 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_1
 *
 */
class Mchpay extends WechatPay {

	/**
	 * 企业付款到零钱
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
	 * @param $partner_trade_no
	 * @param $openid
	 * @param $amount
	 * @param $desc
	 * @param $spbill_create_ip
	 * @param $check_name
	 * @param $re_user_name
	 * @return array
	 * @throws Exception
	 */
	public function transferWallet($partner_trade_no,$openid,$amount,$desc,$spbill_create_ip = null,$re_user_name = null,$check_name = WechatPay::CHECKNAME_FORCECHECK){
		$data = array();
		if($check_name == WechatPay::CHECKNAME_FORCECHECK && !$re_user_name) throw new Exception('Real name is required');
		$data["mch_appid"] = $this->config["app_id"];
		$data["mchid"] = $this->config["mch_id"];
		$data["partner_trade_no"] = $partner_trade_no;
		$data["openid"] = $openid;
		$data["amount"] = $amount;
		$data["desc"] = $desc;
		$data['spbill_create_ip'] = $spbill_create_ip?:$_SERVER['SERVER_ADDR'];
		$data["check_name"] = $check_name;
		$data["re_user_name"] = $re_user_name;
		$result = $this->post(self::URL_TRANSFER_WALLET,$data,true);
		return $result;
	}

	/**
	 * 查询企业付款
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3
	 * @param $partner_trade_no
	 * @return array
	 */
	public function queryTransferWallet($partner_trade_no){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["mch_id"] = $this->config["mch_id"];
		$data["partner_trade_no"] = $partner_trade_no;
		$result = $this->post(self::URL_QUERY_TRANSFER_WALLET,$data,true);
		return $result;
	}

	/**
	 * 企业付款到银行卡
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2
	 * @param $partner_trade_no
	 * @param $bank_no
	 * @param $true_name
	 * @param $bank_code
	 * @param $amount
	 * @param $desc
	 * @return array
	 * @throws Exception
	 */
	public function transferBankCard($partner_trade_no,$bank_no,$true_name,$bank_code,$amount,$desc){
		if(!in_array($bank_code,array_values(self::$BANKCODE))) throw new Exception("Unsupported bank code: $bank_code");
		$data = array();
		$data["partner_trade_no"] = $partner_trade_no;
		$enc_bank_no = $this->rsaEncrypt($bank_no);
		$data["enc_bank_no"] = $enc_bank_no;
		$enc_true_name = $this->rsaEncrypt($true_name);
		$data["enc_true_name"] = $enc_true_name;
		$data["bank_code"] = $bank_code;
		$data["desc"] = $desc;
		$data["amount"] = $amount;
		$result = $this->post(self::URL_TRANSFER_BANKCARD,$data,true);
		return $result;
	}

	/**
	 * 查询企业付款银行卡
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_3
	 * @param $partner_trade_no
	 * @return array
	 */
	public function queryTransferBankCard($partner_trade_no){
		$data = array();
		$data["appid"] = $this->config["app_id"];
		$data["mch_id"] = $this->config["mch_id"];
		$data["partner_trade_no"] = $partner_trade_no;
		$result = $this->post(self::URL_QUERY_TRANSFER_WALLET,$data,true);
		return $result;
	}

	/**
	 * 获取RSA加密公钥
	 * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_7&index=4
	 * @param bool $refresh
	 * @return string
	 * @throws Exception
	 */
	public function getPublicKey($refresh = false){
		if(!$this->publicKey) {
			if (!$refresh && file_exists($this->config["rsa_pubkey_path"])) {
				$this->publicKey = file_get_contents($this->config["rsa_pubkey_path"]);
			}else{
				$data = array();
				$data["mch_id"] = $this->config["mch_id"];
				$data["sign_type"] = $this->config["sign_type"];
				$result = $this->post(self::URL_GETPUBLICKEY, $data, true);
				$pubkey = $result['pub_key'];
				$this->publicKey = $this->convertPKCS1toPKCS8($pubkey);
				if($fp = @fopen($this->config["rsa_pubkey_path"], "w")) {
					fwrite($fp, $this->publicKey);
					fclose($fp);
				}
			}
		}
		return $this->publicKey;
	}

	public function setPublicKey($publicKey){
		$this->publicKey = $publicKey;
	}

	private function convertPKCS1toPKCS8($pkcs1){
		$start_key = $pkcs1;
		$start_key = str_replace('-----BEGIN RSA PUBLIC KEY-----', '', $start_key);
		$start_key = trim(str_replace('-----END RSA PUBLIC KEY-----', '', $start_key));
		$key = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A' . str_replace("\n", '', $start_key);
		$key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
		return $key;
	}
}