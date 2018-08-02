<?php
use zhangv\wechat\pay\WechatPay;
use zhangv\wechat\pay\service\App;
use zhangv\wechat\pay\util\HttpClient;
use zhangv\wechat\pay\util\WechatOAuth;
use PHPUnit\Framework\TestCase;

class RedpackTest extends TestCase {
	/** @var \zhangv\wechat\pay\service\Redpack */
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
		$this->wechatPay = WechatPay::Redpack($config);
		$this->httpClient = $this->createMock(HttpClient::class);
		$this->wechatOauth = $this->createMock(WechatOAuth::class);
		$this->wechatPay->setCacheProvider(new \zhangv\wechat\pay\cache\JsonFileCacheProvider());
	}

	/** @test */
	public function sendRedPack(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[发放成功.]]></return_msg>
				<result_code><![CDATA[SUCCESS]]></result_code>
				<err_code><![CDATA[0]]></err_code>
				<err_code_des><![CDATA[发放成功.]]></err_code_des>
				<mch_billno><![CDATA[0010010404201411170000046545]]></mch_billno>
				<mch_id>10010404</mch_id>
				<wxappid><![CDATA[wx6fa7e3bab7e15415]]></wxappid>
				<re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid>
				<total_amount>1</total_amount>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->sendRedPack(1,'','',1,1,'','actname','remark');
		$this->assertEquals($result['total_amount'],'1');
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 系统繁忙,请稍后再试.
	 */
	public function sendRedPack_fail(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[FAIL]]></return_code>
				<return_msg><![CDATA[系统繁忙,请稍后再试.]]></return_msg>
				<result_code><![CDATA[FAIL]]></result_code>
				<err_code><![CDATA[268458547]]></err_code>
				<err_code_des><![CDATA[系统繁忙,请稍后再试.]]></err_code_des>
				<mch_billno><![CDATA[0010010404201411170000046542]]></mch_billno>
				<mch_id>10010404</mch_id>
				<wxappid><![CDATA[wx6fa7e3bab7e15415]]></wxappid>
				<re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid>
				<total_amount>1</total_amount>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->sendRedPack(1,'','',1,1,'','actname','remark');
	}

	/** @test */
	public function sendGroupRedPack(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[发放成功.]]></return_msg>
				<result_code><![CDATA[SUCCESS]]></result_code>
				<err_code><![CDATA[0]]></err_code>
				<err_code_des><![CDATA[发放成功.]]></err_code_des>
				<mch_billno><![CDATA[0010010404201411170000046545]]></mch_billno>
				<mch_id>10010404</mch_id>
				<wxappid><![CDATA[wx6fa7e3bab7e15415]]></wxappid>
				<re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid>
				<total_amount>3</total_amount>
				<send_time><![CDATA[20150227091010]]></send_time>
				<send_listid><![CDATA[1000000000201502270093647546]]></send_listid>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->sendGroupRedPack(1,'','',1,1,'','actname','remark');
		$this->assertEquals(3,$result['total_amount']);
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 系统繁忙,请稍后再试.
	 */
	public function sendGroupRedPack_fail(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[FAIL]]></return_code>
				<return_msg><![CDATA[系统繁忙,请稍后再试.]]></return_msg>
				<result_code><![CDATA[FAIL]]></result_code>
				<err_code><![CDATA[268458547]]></err_code>
				<err_code_des><![CDATA[系统繁忙,请稍后再试.]]></err_code_des>
				<mch_billno><![CDATA[0010010404201411170000046542]]></mch_billno>
				<mch_id>10010404</mch_id>
				<wxappid><![CDATA[wx6fa7e3bab7e15415]]></wxappid>
				<re_openid><![CDATA[onqOjjmM1tad-3ROpncN-yUfa6uI]]></re_openid>
				<total_amount>3</total_amount>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->sendGroupRedPack(1,'','',1,1,'','actname','remark');
	}

	/** @test */
	public function getHbInfo(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[OK]]></return_msg>
				<result_code><![CDATA[SUCCESS]]></result_code>
				<err_code><![CDATA[SUCCESS]]></err_code>
				<err_code_des><![CDATA[OK]]></err_code_des>
				<mch_billno><![CDATA[9010080799701411170000046603]]></mch_billno>
				<mch_id><![CDATA[11475856]]></mch_id>
				<detail_id><![CDATA[10000417012016080830956240040]]></detail_id>
				<status><![CDATA[RECEIVED]]></status>
				<send_type><![CDATA[ACTIVITY]]></send_type>
				<hb_type><![CDATA[NORMAL]]></hb_type>
				<total_num>1</total_num>
				<total_amount>100</total_amount>
				<send_time><![CDATA[2016-08-08 21:49:22]]></send_time>
				<hblist>
					<hbinfo>
					<openid><![CDATA[oHkLxtzmyHXX6FW_cAWo_orTSRXs]]></openid>
					<amount>100</amount>
					<rcv_time><![CDATA[2016-08-08 21:49:46]]></rcv_time>
					</hbinfo>
				</hblist>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->getHbInfo(1);
		$this->assertEquals(100,$result['total_amount']);
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 指定单号数据不存在
	 */
	public function getHbInfo_fail(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[FAIL]]></return_code>
				<return_msg><![CDATA[指定单号数据不存在]]></return_msg>
				<result_code><![CDATA[FAIL]]></result_code>
				<err_code><![CDATA[SYSTEMERROR]]></err_code>
				<err_code_des><![CDATA[指定单号数据不存在]]></err_code_des>
				<mch_id>666</mch_id>
				<mch_billno><![CDATA[1000005901201407261446939688]]></mch_billno>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->getHbInfo(1);
	}
}
