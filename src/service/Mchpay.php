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
		if($check_name == WechatPay::CHECKNAME_FORCECHECK && !$re_user_name) throw new Exception('Real name is required');
		$data = [
			"mch_appid" => $this->config["app_id"],
			"mchid" => $this->config["mch_id"],
			"partner_trade_no" => $partner_trade_no,
			"openid" => $openid,
			"amount" => $amount,
			"desc" => $desc,
			'spbill_create_ip' => $spbill_create_ip?:$_SERVER['SERVER_ADDR'],
			"check_name" => $check_name,
			"re_user_name" => $re_user_name
		];
		return $this->post(self::URL_TRANSFER_WALLET,$data,true);
	}

	/**
	 * 查询企业付款
	 * @param $partner_trade_no
	 * @return array
	 * @throws Exception
	 */
	public function queryTransferWallet($partner_trade_no){
		$data = [
			"appid" => $this->config["app_id"],
			"mch_id" => $this->config["mch_id"],
			"partner_trade_no" => $partner_trade_no
		];
		return $this->post(self::URL_QUERY_TRANSFER_WALLET,$data,true);
	}

	/**
	 * 企业付款到银行卡
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
		$data = [
			"partner_trade_no" => $partner_trade_no,
			"enc_bank_no" => $this->rsaEncrypt($bank_no),
			"enc_true_name" => $this->rsaEncrypt($true_name),
			"bank_code" => $bank_code,
			"desc" => $desc,
			"amount" => $amount
		];
		return $this->post(self::URL_TRANSFER_BANKCARD,$data,true);
	}

	public function rsaEncrypt($data,$pubkey = null){
		if(!$pubkey) $pubkey = $this->getPublicKey();
		$encrypted = null;
		$pubkey = openssl_get_publickey($pubkey);
		if (@openssl_public_encrypt($data, $encrypted, $pubkey,OPENSSL_PKCS1_OAEP_PADDING))
			$data = base64_encode($encrypted);
		else
			throw new Exception('Unable to encrypt data');
		return $data;
	}

	/**
	 * 查询企业付款银行卡
	 * @param $partner_trade_no
	 * @return array
	 * @throws Exception
	 */
	public function queryTransferBankCard($partner_trade_no){
		$data = [
			"appid" => $this->config["app_id"],
			"mch_id" => $this->config["mch_id"],
			"partner_trade_no" => $partner_trade_no
		];
		return $this->post(self::URL_QUERY_TRANSFER_WALLET,$data,true);
	}

	/**
	 * 获取RSA加密公钥
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