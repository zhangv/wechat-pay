<?php
use zhangv\wechat\pay\WechatPay;
use zhangv\wechat\pay\service\Jsapi;
use zhangv\wechat\pay\util\HttpClient;
use zhangv\wechat\pay\util\WechatOAuth;
use PHPUnit\Framework\TestCase;

class NativeTest extends TestCase {
	/** @var \zhangv\wechat\pay\service\Native */
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
		$this->wechatPay = WechatPay::Native($config);
		$this->httpClient = $this->createMock(HttpClient::class);
		$this->wechatOauth = $this->createMock(WechatOAuth::class);
		$this->wechatPay->setCacheProvider(new \zhangv\wechat\pay\cache\JsonFileCacheProvider());
	}

	/** @test */
	public function getCodeUrl(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code>SUCCESS</return_code>
				<result_code>SUCCESS</result_code>
				<code_url>url</code_url>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->getCodeUrl("", "", 1, 'openid', 'ext');
		$this->assertEquals($result,'url');
	}

}
