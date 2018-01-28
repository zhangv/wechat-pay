<?php
require_once __DIR__ . '/../src/WechatPay.php';
require_once __DIR__ . '/../src/HttpClient.php';
use PHPUnit\Framework\TestCase;
use zhangv\wechat\WechatPay;

class WechatPayTest extends TestCase{
	/**
	 * @var WechatPay
	 */
	private $wechatPay;
	private $openid;
	private $bankNo;

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
			'rsa_pubkey_path' => '/PATHTO/pubkey.pem'
		];
		if(file_exists(__DIR__ . 'config.php'))
			$config = require_once __DIR__ . 'config.php';

		$this->wechatPay = new WechatPay($config);
		$this->openid = "o6JMpuDgLS-L4uvjE6VsIkAEMNM8";
	}

	private static $outTradeNoOffset = 0;
	private function genOutTradeNo(){
		return time().(self::$outTradeNoOffset++);
	}

	public function testGetPrepayId(){
		$outtradeno = $this->genOutTradeNo();
		$result = $this->wechatPay->getPrepayId("test", "$outtradeno", 1, $this->openid, '127.0.0.1','ext');
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertEquals(WechatPay::TRADETYPE_JSAPI,$this->wechatPay->responseArray['trade_type']);
		$this->assertNotNull($result);
	}

	public function testGetCodeUrl(){
		$outtradeno = $this->genOutTradeNo();
		$result = $this->wechatPay->getCodeUrl("test", "$outtradeno", 1, "test{$outtradeno}", 'ext');
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertEquals(WechatPay::TRADETYPE_NATIVE,$this->wechatPay->responseArray['trade_type']);
		$this->assertNotNull($result);
	}

	public function testGetMwebUrl(){//需要开通
		$outtradeno = $this->genOutTradeNo();
		$result = $this->wechatPay->getMwebUrl("test", "$outtradeno", 1,  'ext');
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertEquals(WechatPay::TRADETYPE_MWEB,$this->wechatPay->responseArray['trade_type']);
	}

	public function testMicroPay(){
		$outtradeno = $this->genOutTradeNo();
		$authcode = '120061098828009406';//invalid auth code
		$result = $this->wechatPay->microPay("test", "$outtradeno", 1,  '127.0.0.1',$authcode,[]);
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertEquals('AUTH_CODE_INVALID',$this->wechatPay->responseArray['err_code']);
	}

	public function testCloseOrder(){
		$outtradeno = $this->genOutTradeNo();
		$this->wechatPay->getCodeUrl("test", "$outtradeno", 1, "test{$outtradeno}", 'ext');
		$result = $this->wechatPay->closeOrder($outtradeno);
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testQueryOrderByOutTradeNo(){
		$outtradeno = $this->genOutTradeNo();
		$this->wechatPay->getCodeUrl("test", "$outtradeno", 1, "test{$outtradeno}", 'ext');
		$result = $this->wechatPay->queryOrderByOutTradeNo($outtradeno);
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testDownloadBill(){
		try{
			$this->wechatPay->downloadBill(date('Ymd',time()));
		}catch (Exception $e){}
		$this->assertEquals('FAIL',$this->wechatPay->responseArray['return_code']);
		$this->assertEquals('20002',$this->wechatPay->responseArray['error_code']); //invalid bill_date
	}

	public function testSendRedPack(){
		$outtradeno = $this->genOutTradeNo();
		try{
			$this->wechatPay->sendRedPack($outtradeno,'test',$this->openid,1,1,'wish','act','testcase');
		}catch (Exception $e){}
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertEquals('FAIL',$this->wechatPay->responseArray['result_code']);
		$this->assertEquals('MONEY_LIMIT',$this->wechatPay->responseArray['err_code']);
	}

	public function testSendGroupRedPack(){
		$outtradeno = $this->genOutTradeNo();
		try{
			$this->wechatPay->sendGroupRedPack($outtradeno,'test',$this->openid,1,3,'wish','act','testcase');
		}catch (Exception $e){}
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertEquals('FAIL',$this->wechatPay->responseArray['result_code']);
		$this->assertEquals('MONEY_LIMIT',$this->wechatPay->responseArray['err_code']);
	}

	public function testShortUrl(){
		$result = $this->wechatPay->shortUrl('weixin://wxpay/bizpayurl?sign=XXXXX&appid=XXXXX&mch_id=XXXXX&product_id=XXXXXX&time_stamp=XXXXXX&nonce_str=XXXXX');
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testTransferWallet(){
		$outtradeno = $this->genOutTradeNo();
		$result = $this->wechatPay->transferWallet($outtradeno, $this->openid, 1, 'test','127.0.0.1','zw');
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testQueryTransferWallet(){
		$outtradeno = $this->genOutTradeNo();
		$result = $this->wechatPay->queryTransferWallet($outtradeno);
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testTransferBankCard(){
		$outtradeno = $this->genOutTradeNo();
		$result = $this->wechatPay->transferBankCard($outtradeno, $this->bankNo,'zw', WechatPay::$BANKCODE['招商银行'],1,'test');
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testQueryTransferBankCard(){
		$outtradeno = $this->genOutTradeNo();
		$result = $this->wechatPay->queryTransferBankCard($outtradeno);
		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testGetPublicKey(){
		try{
			$result = $this->wechatPay->getPublicKey(true);
		}catch (Exception $e){
			var_dump($e);
		}

		$this->assertEquals('SUCCESS',$this->wechatPay->responseArray['return_code']);
		$this->assertNotNull($result);
	}

	public function testRSAEncrypt(){
		$result = $this->wechatPay->rsaEncrypt('a');
		$this->assertNotEmpty($result);
	}
}
