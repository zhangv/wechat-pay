<?php
use zhangv\wechat\pay\WechatPay;
use zhangv\wechat\pay\service\Micro;
use zhangv\wechat\pay\util\HttpClient;
use zhangv\wechat\pay\util\WechatOAuth;
use PHPUnit\Framework\TestCase;

class MicroTest extends TestCase {
	/** @var Micro */
	private $wechatPay;
	/** @var HttpClient */
	private $httpClient;
	/** @var WechatOAuth */
	private $wechatOauth;

	public function setUp(){
		$config = [
			'mch_id' => 'XXXX', //商户号
			'app_id' => 'XXXXXXXXXXXXXXXXXXX', //APPID
			'app_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXX', //App Secret
			'api_key' =>'XXXXXXXXXXXXXXXXXXXXXXX', //支付密钥
			'ssl_cert_path' => '/PATHTO/apiclient_cert.pem',
			'ssl_key_path' => '/PATHTO/apiclient_key.pem',
			'sign_type' => 'MD5',
			'notify_url' => 'http://YOURSITE/paidnotify.php',
			'refund_notify_url' => 'http://YOURSITE/refundednotify.php',
			'h5_scene_info' => [//required in H5
				'h5_info' => ['type' => 'Wap', 'wap_url' => 'http://wapurl', 'wap_name' => 'wapname']
			],
			'rsa_pubkey_path' => __DIR__ .'/pubkey.pem',
			'jsapi_ticket' => __DIR__ .'/jsticket.json'
		];
		$this->wechatPay = WechatPay::Micro($config);
		$this->httpClient = $this->createMock(HttpClient::class);
		$this->wechatOauth = $this->createMock(WechatOAuth::class);
		$this->wechatPay->setCacheProvider(new \zhangv\wechat\pay\cache\JsonFileCacheProvider());
	}

	/** @test */
	public function microPay(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
			   <mch_id><![CDATA[10000100]]></mch_id>
			   <device_info><![CDATA[1000]]></device_info>
			   <nonce_str><![CDATA[GOp3TRyMXzbMlkun]]></nonce_str>
			   <sign><![CDATA[D6C76CB785F07992CDE05494BB7DF7FD]]></sign>
			   <result_code><![CDATA[SUCCESS]]></result_code>
			   <openid><![CDATA[oUpF8uN95-Ptaags6E_roPHg7AG0]]></openid>
			   <is_subscribe><![CDATA[Y]]></is_subscribe>
			   <trade_type><![CDATA[MICROPAY]]></trade_type>
			   <bank_type><![CDATA[CCB_DEBIT]]></bank_type>
			   <total_fee>1</total_fee>
			   <coupon_fee>0</coupon_fee>
			   <fee_type><![CDATA[CNY]]></fee_type>
			   <transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id>
			   <out_trade_no><![CDATA[1415757673]]></out_trade_no>
			   <attach><![CDATA[订单额外描述]]></attach>
			   <time_end><![CDATA[20141111170043]]></time_end>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->microPay(1,'','',1,1,'');
		$this->assertEquals(1,$result['total_fee']);
	}

	/** @test */
	public function authCodeToOpenId(){
		$this->httpClient->method('post')->willReturn("<xml>
		   <return_code><![CDATA[SUCCESS]]></return_code>
		   <return_msg><![CDATA[OK]]></return_msg>
		   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
		   <mch_id><![CDATA[10000100]]></mch_id>
		   <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
		   <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
		   <result_code><![CDATA[SUCCESS]]></result_code>
		   <openid><![CDATA[000]]></openid>
		</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->authCodeToOpenId(1);
		$this->assertEquals('000',$result);
	}


	/** @test */
	public function reverse(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
			   <mch_id><![CDATA[10000100]]></mch_id>
			   <nonce_str><![CDATA[o5bAKF3o2ypC8hwa]]></nonce_str>
			   <sign><![CDATA[6F5080EDDD196FFCDE53F786BBB93899]]></sign>
			   <result_code><![CDATA[SUCCESS]]></result_code>
			   <recall><![CDATA[N]]></recall>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->reverseByOutTradeNo(1);
		$this->assertEquals($result['return_code'],'SUCCESS');
		$result = $this->wechatPay->reverseByTransactionId(1);
		$this->assertEquals($result['return_code'],'SUCCESS');
	}

}
