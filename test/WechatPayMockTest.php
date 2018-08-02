<?php
use PHPUnit\Framework\TestCase;
use zhangv\wechat\pay\WechatPay;
use zhangv\wechat\pay\util\HttpClient;
use zhangv\wechat\pay\util\WechatOAuth;

class WechatPayMockTest extends TestCase{
	/** @var WechatPay */
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
		$this->wechatPay = new WechatPay($config);
		$this->httpClient = $this->createMock(HttpClient::class);
		$this->wechatOauth = $this->createMock(WechatOAuth::class);
		$this->wechatPay->setCacheProvider(new \zhangv\wechat\pay\cache\JsonFileCacheProvider());
	}

	/** @test */
	public function queryOrder(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
			   <mch_id><![CDATA[10000100]]></mch_id>
			   <device_info><![CDATA[1000]]></device_info>
			   <nonce_str><![CDATA[TN55wO9Pba5yENl8]]></nonce_str>
			   <sign><![CDATA[BDF0099C15FF7BC6B1585FBB110AB635]]></sign>
			   <result_code><![CDATA[SUCCESS]]></result_code>
			   <openid><![CDATA[oUpF8uN95-Ptaags6E_roPHg7AG0]]></openid>
			   <is_subscribe><![CDATA[Y]]></is_subscribe>
			   <trade_type><![CDATA[MICROPAY]]></trade_type>
			   <bank_type><![CDATA[CCB_DEBIT]]></bank_type>
			   <total_fee>1</total_fee>
			   <fee_type><![CDATA[CNY]]></fee_type>
			   <transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id>
			   <out_trade_no><![CDATA[1415757673]]></out_trade_no>
			   <attach><![CDATA[订单额外描述]]></attach>
			   <time_end><![CDATA[20141111170043]]></time_end>
			   <trade_state><![CDATA[SUCCESS]]></trade_state>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->queryOrderByTransactionId(1);
		$this->assertEquals($result['return_code'],'SUCCESS');

		$result = $this->wechatPay->queryOrderByOutTradeNo(1);
		$this->assertEquals($result['return_code'],'SUCCESS');
	}
	/**
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 此交易订单号不存在
	 */
	public function queryOrder_notexist(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
			   <mch_id><![CDATA[10000100]]></mch_id>
			   <nonce_str><![CDATA[TN55wO9Pba5yENl8]]></nonce_str>
			   <sign><![CDATA[BDF0099C15FF7BC6B1585FBB110AB635]]></sign>
			   <result_code><![CDATA[FAIL]]></result_code>
			   <err_code_des><![CDATA[此交易订单号不存在]]></err_code_des>
			   <err_code><![CDATA[ORDERNOTEXIST]]></err_code>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->queryOrderByTransactionId(1);
	}

	/** @test */
	public function closeOrder(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
			   <mch_id><![CDATA[10000100]]></mch_id>
			   <nonce_str><![CDATA[BFK89FC6rxKCOjLX]]></nonce_str>
			   <sign><![CDATA[72B321D92A7BFA0B2509F3D13C7B1631]]></sign>
			   <result_code><![CDATA[SUCCESS]]></result_code>
			   <result_msg><![CDATA[OK]]></result_msg>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->closeOrder(1);
		$this->assertEquals($result['result_msg'],'OK');
	}

	/** @test */
	public function refund(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
			   <mch_id><![CDATA[10000100]]></mch_id>
			   <nonce_str><![CDATA[NfsMFbUFpdbEhPXP]]></nonce_str>
			   <sign><![CDATA[B7274EB9F8925EB93100DD2085FA56C0]]></sign>
			   <result_code><![CDATA[SUCCESS]]></result_code>
			   <transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id>
			   <out_trade_no><![CDATA[1415757673]]></out_trade_no>
			   <out_refund_no><![CDATA[1415701182]]></out_refund_no>
			   <refund_id><![CDATA[2008450740201411110000174436]]></refund_id>
			   <refund_channel><![CDATA[]]></refund_channel>
			   <refund_fee>1</refund_fee> 
			</xml>
			");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->refundByOutTradeNo(1,1,1,1);
		$this->assertEquals($result['refund_fee'],1);
		$result = $this->wechatPay->refundByTransactionId(1,1,1,1);
		$this->assertEquals($result['refund_fee'],1);
	}

	/** @test */
	public function queryRefund(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
			   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
			   <mch_id><![CDATA[10000100]]></mch_id>
			   <nonce_str><![CDATA[TeqClE3i0mvn3DrK]]></nonce_str>
			   <out_refund_no_0><![CDATA[1415701182]]></out_refund_no_0>
			   <out_trade_no><![CDATA[1415757673]]></out_trade_no>
			   <refund_count>1</refund_count>
			   <refund_fee_0>1</refund_fee_0>
			   <refund_id_0><![CDATA[2008450740201411110000174436]]></refund_id_0>
			   <refund_status_0><![CDATA[PROCESSING]]></refund_status_0>
			   <result_code><![CDATA[SUCCESS]]></result_code>
			   <return_code><![CDATA[SUCCESS]]></return_code>
			   <return_msg><![CDATA[OK]]></return_msg>
			   <sign><![CDATA[1F2841558E233C33ABA71A961D27561C]]></sign>
			   <transaction_id><![CDATA[1008450740201411110005820873]]></transaction_id>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->queryRefundByOutRefundNo(1);
		$this->assertEquals($result['return_code'],'SUCCESS');
		$result = $this->wechatPay->queryRefundByOutTradeNo(1);
		$this->assertEquals($result['return_code'],'SUCCESS');
		$result = $this->wechatPay->queryRefundByRefundId(1);
		$this->assertEquals($result['return_code'],'SUCCESS');
		$result = $this->wechatPay->queryRefundByTransactionId(1);
		$this->assertEquals($result['return_code'],'SUCCESS');
	}

	/** @test */
	public function downloadBill(){
		$mock = "交易时间,公众账号ID,商户号,子商户号,设备号,微信订单号,商户订单号,用户标识,交易类型,交易状态,付款银行,货币种类,总金额,代金券或立减优惠金额,微信退款单号,商户退款单号,退款金额,代金券或立减优惠退款金额,退款类型,退款状态,商品名称,商户数据包,手续费,费率
			`2014-11-1016：33：45,`wx2421b1c4370ec43b,`10000100,`0,`1000,`1001690740201411100005734289,`1415640626,`085e9858e3ba5186aafcbaed1,`MICROPAY,`SUCCESS,`CFT,`CNY,`0.01,`0.0,`0,`0,`0,`0,`,`,`被扫支付测试,`订单额外描述,`0,`0.60%
			`2014-11-1016：46：14,`wx2421b1c4370ec43b,`10000100,`0,`1000,`1002780740201411100005729794,`1415635270,`085e9858e90ca40c0b5aee463,`MICROPAY,`SUCCESS,`CFT,`CNY,`0.01,`0.0,`0,`0,`0,`0,`,`,`被扫支付测试,`订单额外描述,`0,`0.60%
			 总交易单数,总交易额,总退款金额,总代金券或立减优惠退款金额,手续费总金额
			`2,`0.02,`0.0,`0.0,`0";
		$this->httpClient->method('post')->willReturn($mock);
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->downloadBill(1);
		$this->assertEquals($mock,$result);
	}

	/** @test */
	public function downloadFundFlow(){
		$mock = "记账时间,微信支付业务单号,资金流水单号,业务名称,业务类型,收支类型,收支金额（元）,账户结余（元）,资金变更提交申请人,备注,业务凭证号
				`2018-02-01 04:21:23,`50000305742018020103387128253,`1900009231201802015884652186,`退款,`退款,`支出,`0.02,`0.17,`system,`缺货,`REF4200000068201801293084726067
				资金流水总笔数,收入笔数,收入金额,支出笔数,支出金额
				`20.0,`17.0,`0.35,`3.0,`0.18";
		$this->httpClient->method('post')->willReturn($mock);
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->downloadFundFlow(1);
		$this->assertEquals($mock,$result);
	}

	/** @test */
	public function sign(){
		$data = [
			'appid' =>	'wxd930ea5d5a258f4f',
			'mch_id'=>	'10000100',
			'device_info'=>	'1000',
			'body'=> 'test',
			'nonce_str'=>'ibuaiVcKdpRxkhJA'];
		$this->wechatPay->setConfig(['api_key'=>'192006250b4c09247ec02edce69f6a2d']);
		$md5 = $this->wechatPay->sign($data,WechatPay::SIGNTYPE_MD5);
		$this->assertEquals('9A0A8659F005D6984697E2CA0A9CF3B7',$md5);
		$sha256 = $this->wechatPay->sign($data,WechatPay::SIGNTYPE_HMACSHA256);
		$this->assertEquals('6A9AE1657590FD6257D693A078E1C3E4BB6BA4DC30B23E0EE2496E54170DACD6',$sha256);
	}
	/** @test */
	public function validateSign(){
		$data = ['a'=>'b'];
		$this->assertFalse($this->wechatPay->validateSign($data));
	}

	/**
	 * @test
	 * @expectedException Exception
	 */
	public function sign_notsupportedmethod(){
		$data = ['a'=>'b'];
		$this->wechatPay->sign($data,'nomethod');
	}

	/** @test */
	public function getTicket(){
		$this->wechatOauth->method('getTicket')->willReturn(
			json_decode('{
			"errcode":0,
			"errmsg":"ok",
			"ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA",
			"expires_in":7200
			}'));
		$this->wechatPay->setWechatOAuth($this->wechatOauth);
		$t = $this->wechatPay->getTicket(true);
		$this->assertEquals('bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA',$t);
		$t = $this->wechatPay->getTicket(true);
		$this->assertEquals('bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA',$t);
		$this->wechatPay->getCacheProvider()->clear('jsapi_ticket');
		$this->wechatPay->setWechatOAuth(null);
		$this->wechatPay->getWechatOAuth();
	}

	/** @test */
	public function batchQueryComment(){
		$comments = "100
`2017-07-01 10:00:05,` 1001690740201411100005734289,`5,`赞，水果很新鲜 
`2017-07-01 11:00:05,` 1001690740201411100005734278,`5,`不错，支付渠道很方便 
`2017-07-01 11:30:05,` 1001690740201411100005734250,`4,`东西还算符合预期";
		$this->httpClient->method('post')->willReturn($comments);
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->batchQueryComment(1,1);
		$this->assertEquals($comments,$result);
	}

	/** @test */
	public function getSignPackage(){
		$sp = $this->wechatPay->getSignPackage('url','ticket');
		$this->assertEquals($sp['appId'],$this->wechatPay->getConfig()['app_id']);
	}

	/** @test */
	public function getSignKey(){
		$this->httpClient->method('post')->willReturn("<xml>
		   <return_code><![CDATA[SUCCESS]]></return_code>
		   <return_msg><![CDATA[OK]]></return_msg>
		   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
		   <mch_id><![CDATA[10000100]]></mch_id>
		   <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
		   <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
		   <result_code><![CDATA[SUCCESS]]></result_code>
		   <sandbox_signkey><![CDATA[000]]></sandbox_signkey>
		</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->getSignKey();
		$this->assertEquals('000',$result);
	}

	/** @test */
	public function shortUrl(){
		$this->httpClient->method('post')->willReturn("<xml>
		   <return_code><![CDATA[SUCCESS]]></return_code>
		   <return_msg><![CDATA[OK]]></return_msg>
		   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
		   <mch_id><![CDATA[10000100]]></mch_id>
		   <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
		   <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
		   <result_code><![CDATA[SUCCESS]]></result_code>
		   <short_url><![CDATA[000]]></short_url>
		</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->shortUrl(1);
		$this->assertEquals('000',$result);
	}

	/** @test */
	public function report(){
		$this->httpClient->method('post')->willReturn("<xml>
		   <return_code><![CDATA[SUCCESS]]></return_code>
		   <return_msg><![CDATA[OK]]></return_msg>
		   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
		   <mch_id><![CDATA[10000100]]></mch_id>
		   <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
		   <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
		   <result_code><![CDATA[SUCCESS]]></result_code>
		</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->report(1,1,1,1,1);
		$this->assertEquals('SUCCESS',$result['return_code']);
	}

	/** @test */
	public function responseNotify(){
		$s = $this->wechatPay->responseNotify(false,[]);
		$this->assertEquals("<xml>
<return_code><![CDATA[SUCCESS]]></return_code>
<return_msg><![CDATA[OK]]></return_msg>
</xml>",$s);
	}

	/**
	 * 系统错误
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 签名失败
	 */
	public function systemErr(){
		$this->httpClient->method('post')->willReturn(
			"<xml><return_code>FAIL</return_code><return_msg>签名失败</return_msg></xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->unifiedOrder([
			'body' => 'a','total_fee' => 1, 'openid'=>'a', 'trade_type' => WechatPay::TRADETYPE_JSAPI , 'spbill_create_ip' => '127.0.0.1'
		]);
	}

	/**
	 * 应用错误
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage 商户无此接口权限
	 */
	public function applicationErr(){
		$this->httpClient->method('post')->willReturn(
			"<xml><return_code>SUCCESS</return_code><return_msg>OK</return_msg><result_code>FAIL</result_code>
			<err_code>NOAUTH</err_code><err_code_des>商户无此接口权限</err_code_des></xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->unifiedOrder([
			'body' => 'a','total_fee' => 1, 'openid'=>'a', 'trade_type' => WechatPay::TRADETYPE_JSAPI , 'spbill_create_ip' => '127.0.0.1'
		]);
	}

	/**
	 * 应用错误
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessage No return code presents
	 */
	public function application_NoReturnCode(){
		$this->httpClient->method('post')->willReturn(
			"<xml><return_msg>OK</return_msg><result_code>FAIL</result_code>
			<err_code>NOAUTH</err_code><err_code_des>缺少returcode</err_code_des></xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$this->wechatPay->unifiedOrder([
			'body' => 'a','total_fee' => 1, 'openid'=>'a', 'trade_type' => WechatPay::TRADETYPE_JSAPI , 'spbill_create_ip' => '127.0.0.1'
		]);
	}

	/**
	 * 支付结果返回
	 * @test
	 */
	public function onPaidNotify(){
		$notifydata = ['a'=>'b'];
		$notifydata['sign'] = $this->wechatPay->sign($notifydata);
		$notifyxml = "<xml><a>b</a><sign>{$notifydata['sign']}</sign></xml>";
		$r = $this->wechatPay->onPaidNotify($notifyxml, function($data){
			return $data;
		});
		$this->assertEquals('b',$r['a']);
	}
	/** @test */
	public function onRefundedNotify(){
		$notifydata = ['a'=>'b'];
		$notifydata['sign'] = $this->wechatPay->sign($notifydata);
		$notifyxml = "<xml><a>b</a><sign>{$notifydata['sign']}</sign></xml>";
		$r = $this->wechatPay->onRefundedNotify($notifyxml, function($data){
			return $data;
		});
		$this->assertEquals($notifydata['sign'],$r['sign']);
	}

	/**
	 * 支付结果返回异常
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /Invalid paid notify data/
	 */
	public function onPaidNotifyException(){
		$notifydata = ['a'=>'b'];
		$sign = 'wrong sign';
		$notifydata['sign'] = $sign;
		$this->wechatPay->onPaidNotify($notifydata, function($data){
			return $data;
		});
	}

	/**
	 * 退款结果返回异常
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /Invalid refund notify data/
	 */
	public function onRefundNotifyException(){
		$notifydata = ['a'=>'b'];
		$sign = 'wrong sign';
		$notifydata['sign'] = $sign;
		$this->wechatPay->onRefundedNotify($notifydata, function($data){
			return $data;
		});
	}

}
