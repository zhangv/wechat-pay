<?php
use zhangv\wechat\pay\WechatPay;
use zhangv\wechat\pay\service\App;
use zhangv\wechat\pay\util\HttpClient;
use zhangv\wechat\pay\util\WechatOAuth;
use PHPUnit\Framework\TestCase;

class MchpayTest extends TestCase {
	/** @var \zhangv\wechat\pay\service\Mchpay */
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
		$this->wechatPay = WechatPay::Mchpay($config);
		$this->httpClient = $this->createMock(HttpClient::class);
		$this->wechatOauth = $this->createMock(WechatOAuth::class);
		$this->wechatPay->setCacheProvider(new \zhangv\wechat\pay\cache\JsonFileCacheProvider());
	}

	/** @test */
	public function transferWallet(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[]]></return_msg>
				<mch_appid><![CDATA[wxec38b8ff840bd989]]></mch_appid>
				<mchid><![CDATA[10013274]]></mchid>
				<device_info><![CDATA[]]></device_info>
				<nonce_str><![CDATA[lxuDzMnRjpcXzxLx0q]]></nonce_str>
				<result_code><![CDATA[SUCCESS]]></result_code>
				<partner_trade_no><![CDATA[10013574201505191526582441]]></partner_trade_no>
				<payment_no><![CDATA[1000018301201505190181489473]]></payment_no>
				<payment_time><![CDATA[2015-05-19 15：26：59]]></payment_time>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->transferWallet(1,'','',1,1,'zw');
		$this->assertEquals('SUCCESS',$result['return_code']);
	}
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 系统繁忙,请稍后再试.
	 */
	public function transferWallet_fail(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[FAIL]]></return_code>
				<return_msg><![CDATA[系统繁忙,请稍后再试.]]></return_msg>
				<result_code><![CDATA[FAIL]]></result_code>
				<err_code><![CDATA[SYSTEMERROR]]></err_code>
				<err_code_des><![CDATA[系统繁忙,请稍后再试.]]></err_code_des>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->transferWallet(1,'','',1,1,'zw');
	}

	/** @test */
	public function queryTransferWallet(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[获取成功]]></return_msg>
				<result_code><![CDATA[SUCCESS]]></result_code>
				<mch_id>10000098</mch_id>
				<appid><![CDATA[wxe062425f740c30d8]]></appid>
				<detail_id><![CDATA[1000000000201503283103439304]]></detail_id>
				<partner_trade_no><![CDATA[1000005901201407261446939628]]></partner_trade_no>
				<status><![CDATA[SUCCESS]]></status>
				<payment_amount>650</payment_amount >
				<openid ><![CDATA[oxTWIuGaIt6gTKsQRLau2M0yL16E]]></openid>
				<transfer_time><![CDATA[2015-04-21 20:00:00]]></transfer_time>
				<transfer_name ><![CDATA[测试]]></transfer_name >
				<desc><![CDATA[福利测试]]></desc>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->queryTransferWallet(1);
		$this->assertEquals('SUCCESS',$result['return_code']);
	}

	/** @test */
	public function transferBankCard(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[支付成功]]></return_msg>
				<result_code><![CDATA[SUCCESS]]></result_code>
				<err_code><![CDATA[SUCCESS]]></err_code>
				<err_code_des><![CDATA[微信侧受理成功]]></err_code_des>
				<nonce_str><![CDATA[50780e0cca98c8c8e814883e5caa672e]]></nonce_str>
				<mch_id><![CDATA[2302758702]]></mch_id>
				<partner_trade_no><![CDATA[1212121221278]]></partner_trade_no>
				<amount>500</amount>
				<payment_no><![CDATA[10000600500852017030900000020006012]]></payment_no>
				<cmms_amt>0</cmms_amt>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->setPublicKey('-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArT82k67xybiJS9AD8nNA
euDYdrtCRaxkS6cgs8L9h83eqlDTlrdwzBVSv5V4imTq/URbXn4K0V/KJ1TwDrqO
I8hamGB0fvU13WW1NcJuv41RnJVua0QAlS3tS1JzOZpMS9BEGeFvyFF/epbi/m9+
2kUWG94FccArNnBtBqqvFncXgQsm98JB3a62NbS1ePP/hMI7Kkz+JNMyYsWkrOUF
DCXAbSZkWBJekY4nGZtK1erqGRve8JbxTWirAm/s08rUrjOuZFA21/EI2nea3Did
JMTVnXVPY2qcAjF+595shwUKyTjKB8v1REPB3hPF1Z75O6LwuLfyPiCrCTmVoyfq
jwIDAQAB
-----END PUBLIC KEY-----');
		$result = $this->wechatPay->transferBankCard(1,'','','1001',1,'zw');
		$this->assertEquals('SUCCESS',$result['return_code']);
	}
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 系统繁忙,请稍后再试.
	 */
	public function transferBankCard_fail(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[FAIL]]></return_code>
				<return_msg><![CDATA[系统繁忙,请稍后再试.]]></return_msg>
				<result_code><![CDATA[FAIL]]></result_code>
				<err_code><![CDATA[SYSTEMERROR]]></err_code>
				<err_code_des><![CDATA[系统繁忙,请稍后再试.]]></err_code_des>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->transferBankCard(1,'','','1001',1,'zw');
	}

	/** @test */
	public function queryTransferBankCard(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code><![CDATA[SUCCESS]]></return_code>
				<return_msg><![CDATA[ok]]></return_msg>
				<result_code><![CDATA[SUCCESS]]></result_code>
				<err_code><![CDATA[SUCCESS]]></err_code>
				<err_code_des><![CDATA[ok]]></err_code_des>
				<mch_id><![CDATA[2302758702]]></mch_id>
				<partner_trade_no><![CDATA[1212121221278]]></partner_trade_no>
				<payment_no><![CDATA[10000600500852017030900000020006012]]></payment_no>
				<bank_no_md5><![CDATA[2260AB5EF3D290E28EFD3F74FF7A29A0]]></bank_no_md5>
				<true_name_md5><![CDATA[7F25B325D37790764ABA55DAD8D09B76]]></true_name_md5>
				<amount>500</amount>
				<status><![CDATA[处理中]]></status>
				<cmms_amt>0</cmms_amt>
				<create_time><![CDATA[2017-03-09 15:04:04]]></create_time>
				<reason><![CDATA[]]></reason>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->queryTransferBankCard(1);
		$this->assertEquals('SUCCESS',$result['return_code']);
	}

	/** @test */
	public function getPublicKey(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code>SUCCESS</return_code>
				<return_msg>OK</return_msg>
				<result_code>SUCCESS</result_code>
				<mch_id>123456</mch_id>
				<pub_key><![CDATA[-----BEGIN RSA PUBLIC KEY-----
MIIBCgKCAQEArT82k67xybiJS9AD8nNAeuDYdrtCRaxkS6cgs8L9h83eqlDTlrdw
zBVSv5V4imTq/URbXn4K0V/KJ1TwDrqOI8hamGB0fvU13WW1NcJuv41RnJVua0QA
lS3tS1JzOZpMS9BEGeFvyFF/epbi/m9+2kUWG94FccArNnBtBqqvFncXgQsm98JB
3a62NbS1ePP/hMI7Kkz+JNMyYsWkrOUFDCXAbSZkWBJekY4nGZtK1erqGRve8Jbx
TWirAm/s08rUrjOuZFA21/EI2nea3DidJMTVnXVPY2qcAjF+595shwUKyTjKB8v1
REPB3hPF1Z75O6LwuLfyPiCrCTmVoyfqjwIDAQAB
-----END RSA PUBLIC KEY-----
]]></pub_key>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->sandbox = true;
		$result = $this->wechatPay->getPublicKey(true);
		$this->assertEquals('-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArT82k67xybiJS9AD8nNA
euDYdrtCRaxkS6cgs8L9h83eqlDTlrdwzBVSv5V4imTq/URbXn4K0V/KJ1TwDrqO
I8hamGB0fvU13WW1NcJuv41RnJVua0QAlS3tS1JzOZpMS9BEGeFvyFF/epbi/m9+
2kUWG94FccArNnBtBqqvFncXgQsm98JB3a62NbS1ePP/hMI7Kkz+JNMyYsWkrOUF
DCXAbSZkWBJekY4nGZtK1erqGRve8JbxTWirAm/s08rUrjOuZFA21/EI2nea3Did
JMTVnXVPY2qcAjF+595shwUKyTjKB8v1REPB3hPF1Z75O6LwuLfyPiCrCTmVoyfq
jwIDAQAB
-----END PUBLIC KEY-----',$result);
		$this->wechatPay->setPublicKey(null);//unset the pubkey
		$result = $this->wechatPay->getPublicKey();
		$this->assertEquals('-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArT82k67xybiJS9AD8nNA
euDYdrtCRaxkS6cgs8L9h83eqlDTlrdwzBVSv5V4imTq/URbXn4K0V/KJ1TwDrqO
I8hamGB0fvU13WW1NcJuv41RnJVua0QAlS3tS1JzOZpMS9BEGeFvyFF/epbi/m9+
2kUWG94FccArNnBtBqqvFncXgQsm98JB3a62NbS1ePP/hMI7Kkz+JNMyYsWkrOUF
DCXAbSZkWBJekY4nGZtK1erqGRve8JbxTWirAm/s08rUrjOuZFA21/EI2nea3Did
JMTVnXVPY2qcAjF+595shwUKyTjKB8v1REPB3hPF1Z75O6LwuLfyPiCrCTmVoyfq
jwIDAQAB
-----END PUBLIC KEY-----',$result);
		unlink($this->wechatPay->getConfig()['rsa_pubkey_path']);
	}

	/**
	 * @test
	 */
	public function rsaEncrypt(){
		$pubkey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArT82k67xybiJS9AD8nNA
euDYdrtCRaxkS6cgs8L9h83eqlDTlrdwzBVSv5V4imTq/URbXn4K0V/KJ1TwDrqO
I8hamGB0fvU13WW1NcJuv41RnJVua0QAlS3tS1JzOZpMS9BEGeFvyFF/epbi/m9+
2kUWG94FccArNnBtBqqvFncXgQsm98JB3a62NbS1ePP/hMI7Kkz+JNMyYsWkrOUF
DCXAbSZkWBJekY4nGZtK1erqGRve8JbxTWirAm/s08rUrjOuZFA21/EI2nea3Did
JMTVnXVPY2qcAjF+595shwUKyTjKB8v1REPB3hPF1Z75O6LwuLfyPiCrCTmVoyfq
jwIDAQAB
-----END PUBLIC KEY-----';
		$result = $this->wechatPay->rsaEncrypt('a',$pubkey);
		$this->assertNotEmpty($result);
	}

	/**
	 * @test
	 * @expectedException Exception
	 */
	public function rsaEncryptFail(){
		$pubkey = 'fakekey';
		$result = $this->wechatPay->rsaEncrypt('a',$pubkey);
		$this->assertNotEmpty($result);
	}

}
