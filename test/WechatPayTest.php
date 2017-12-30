<?php
require __DIR__ . '/../WechatPay.php';
use PHPUnit\Framework\TestCase;

class WechatPayTest extends TestCase{
	/**
	 * @var WechatPay
	 */
	private $wechatPay;
	/**
	 * @var HttpClient
	 */
	private $httpClient;

	public function setUp(){
		$config = [
			'mch_id' => 'XXXX', //商户号
			'appid' => 'XXXXXXXXXXXXXXXXXXX', //APPID
			'appsecret' => 'XXXXXXXXXXXXXXXXXXXXXXXXX', //App Secret
			'apikey' =>'XXXXXXXXXXXXXXXXXXXXXXX', //支付密钥
			'sslcertPath' => '/PATHTO/apiclient_cert.pem',
			'sslkeyPath' => '/PATHTO/apiclient_key.pem',
			'signType' => 'MD5',
			'notify_url' => 'http://YOURSITE/paidnotify.php',
			'refundnotify_url' => 'http://YOURSITE/refundednotify.php',
		];
		$this->wechatPay = new WechatPay($config);
		$this->httpClient = $this->createMock(HttpClient::class);
	}

	public function testGetPrepayId(){
		$this->httpClient->method('post')->willReturn(
			"<xml><return_code>SUCCESS</return_code><result_code>SUCCESS</result_code><prepay_id>fakeprepay_id</prepay_id></xml>");
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->getPrepayId("", "", 1, 'openid', 'ext');
		$this->assertEquals($result,'fakeprepay_id');
	}

	/**
	 * 系统错误
	 * @expectedException Exception
	 * @expectedExceptionMessage 签名失败
	 */
	public function testSysErr(){
		$this->httpClient->method('post')->willReturn(
			"<xml><return_code>FAIL</return_code><return_msg>签名失败</return_msg></xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->getPrepayId("", "", 1, 'openid', 'ext');
	}

	/**
	 * 应用错误
	 * @expectedException Exception
	 * @expectedExceptionMessage 商户无此接口权限
	 */
	public function testApplicationErr(){
		$this->httpClient->method('post')->willReturn(
			"<xml><return_code>SUCCESS</return_code><return_msg>OK</return_msg><result_code>FAIL</result_code>
			<err_code>NOAUTH</err_code><err_code_des>商户无此接口权限</err_code_des></xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->getPrepayId("", "", 1, 'openid', 'ext');
	}

	public function testOnPaidNotify(){
		$notifydata = ['a'=>'b'];
		$sign = $this->wechatPay->sign($notifydata);
		$notifydata['sign'] = $sign;
		$r = $this->wechatPay->onPaidNotify($notifydata, function($data){
			return $data;
		});
		$this->assertEquals($sign,$r['sign']);
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /Invalid paid notify data/
	 */
	public function testOnPaidNotifyException(){
		$notifydata = ['a'=>'b'];
		$sign = 'wrong sign';
		$notifydata['sign'] = $sign;
		$this->wechatPay->onPaidNotify($notifydata, function($data){
			return $data;
		});
	}
}
