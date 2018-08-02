<?php
use zhangv\wechat\pay\WechatPay;
use zhangv\wechat\pay\service\App;
use zhangv\wechat\pay\util\HttpClient;
use zhangv\wechat\pay\util\WechatOAuth;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase {
	/** @var \zhangv\wechat\pay\service\Coupon */
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
		$this->wechatPay = WechatPay::Coupon($config);
		$this->httpClient = $this->createMock(HttpClient::class);
		$this->wechatOauth = $this->createMock(WechatOAuth::class);
		$this->wechatPay->setCacheProvider(new \zhangv\wechat\pay\cache\JsonFileCacheProvider());
	}

	/** @test */
	public function sendCoupon(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code>SUCCESS</return_code>
				<appid>wx5edab3bdfba3dc1c</appid>
				<mch_id>10000098</mch_id>
				<nonce_str>1417579335</nonce_str>
				<sign>841B3002FE2220C87A2D08ABD8A8F791</sign>
				<result_code>SUCCESS</result_code>
				<coupon_stock_id>1717</coupon_stock_id>
				<resp_count>1</resp_count>
				<success_count>1</success_count>
				<failed_count>0</failed_count>
				<openid>onqOjjrXT-776SpHnfexGm1_P7iE</openid>
				<ret_code>SUCCESS</ret_code>
				<coupon_id>6954</coupon_id>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->sendCoupon(1,1,1);
		$this->assertEquals('SUCCESS',$result['return_code']);
	}

	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 你已领取过该代金券
	 */
	public function sendCoupon_fail(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code>SUCCESS</return_code>
				<appid>wx5edab3bdfba3dc1c</appid>
				<mch_id>10000098</mch_id>
				<nonce_str>1417579335</nonce_str>
				<sign>841B3002FE2220C87A2D08ABD8A8F791</sign>
				<result_code>FAIL</result_code>
				<err_code>268456007</err_code>
				<err_code_des>你已领取过该代金券</err_code_des>
				<coupon_stock_id>1717</coupon_stock_id>
				<resp_count>1</resp_count>
				<success_count>0</success_count>
				<failed_count>1</failed_count>
				<openid>onqOjjrXT-776SpHnfexGm1_P7iE</openid>
				<ret_code>FAIL</ret_code>
				<ret_msg>你已领取过该代金券</ret_msg>
				<coupon_id></coupon_id>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->sendCoupon(1,1,1);
		$this->assertEquals('FAIL',$result['result_code']);
	}

	/** @test */
	public function queryCouponStock(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				 <return_code>SUCCESS</return_code>
				  <appid>wx5edab3bdfba3dc1c</appid>
				  <mch_id>10000098</mch_id>
				  <nonce_str>1417583379</nonce_str>
				  <sign>841B3002FE2220C87A2D08ABD8A8F791</sign>
				  <result_code>SUCCESS</result_code>
				  <coupon_stock_id>1717</coupon_stock_id>
				  <coupon_value>5</coupon_value>
				  <coupon_mininumn>10</coupon_mininumn>
				  <coupon_stock_status>4</coupon_stock_status>
				  <coupon_total>100</coupon_total>
				  <coupon_budget>500</coupon_budget>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->queryCouponStock(1,1);
		$this->assertEquals('SUCCESS',$result['return_code']);
	}
	/** @test */
	public function queryCouponsInfo(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code>SUCCESS</return_code>
				  <appid>wx5edab3bdfba3dc1c</appid>
				  <mch_id>10000098</mch_id>
				  <nonce_str>1417586982</nonce_str>
				  <sign>841B3002FE2220C87A2D08ABD8A8F791</sign>
				  <result_code>SUCCESS</result_code>
				  <coupon_stock_id>1717</coupon_stock_id> 
				  <coupon_id>1442</coupon_id>
				  <coupon_value>5</coupon_value>
				  <coupon_mininum>10</coupon_mininum>
				  <coupon_name>测试代金券</coupon_name>
				  <coupon_state>SENDED</coupon_state> 
				  <coupon_desc>微信支付-代金券</coupon_desc>
				  <coupon_use_value>0</coupon_use_value>
				  <coupon_remain_value>5</coupon_remain_value>
				  <send_source>FULL_SEND</send_source>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->queryCouponsInfo(1,1,1);
		$this->assertEquals('SUCCESS',$result['return_code']);
	}
}
