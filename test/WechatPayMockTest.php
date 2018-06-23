<?php
use PHPUnit\Framework\TestCase;
use zhangv\wechat\WechatPay;
use zhangv\wechat\HttpClient;
use zhangv\wechat\WechatOAuth;

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
		$this->wechatPay->setCacheProvider(new \zhangv\wechat\cache\JsonFileCacheProvider());
	}

	/** @test */
	public function getPrepayId(){
		$this->httpClient->method('post')->willReturn(
			"<xml><return_code>SUCCESS</return_code><result_code>SUCCESS</result_code><prepay_id>fakeprepay_id</prepay_id></xml>");
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->getPrepayId("", "", 1, 'openid', 'ext');
		$this->assertEquals($result,'fakeprepay_id');
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

	/** @test */
	public function getMwebUrl(){
		$this->httpClient->method('post')->willReturn(
			"<xml>
				<return_code>SUCCESS</return_code>
				<result_code>SUCCESS</result_code>
				<mweb_url>url</mweb_url>
				<trade_type>MWEB</trade_type>
				<prepay_id>xxx</prepay_id>
			</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);

		$result = $this->wechatPay->getMwebUrl("", "", 1, 'ext');
		$this->assertEquals($result,'url');
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
	public function getPackage(){
		$r = $this->wechatPay->getPackage('1',WechatPay::TRADETYPE_JSAPI);
		$this->assertEquals("prepay_id=1",$r['package']);
		$r = $this->wechatPay->getPackage('1',WechatPay::TRADETYPE_APP);
		$this->assertEquals("Sign=WXPay",$r['package']);
		$this->assertEquals($this->wechatPay->getConfig()['app_id'],$r['appid']);
	}

	/** @test */
	public function getPrepayIdAPP(){
		$this->httpClient->method('post')->willReturn("<xml>
		   <return_code><![CDATA[SUCCESS]]></return_code>
		   <return_msg><![CDATA[OK]]></return_msg>
		   <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
		   <mch_id><![CDATA[10000100]]></mch_id>
		   <nonce_str><![CDATA[IITRi8Iabbblz1Jc]]></nonce_str>
		   <sign><![CDATA[7921E432F65EB8ED0CE9755F0E86D72F]]></sign>
		   <result_code><![CDATA[SUCCESS]]></result_code>
		   <prepay_id><![CDATA[wx201411101639507cbf6ffd8b0779950874]]></prepay_id>
		   <trade_type><![CDATA[APP]]></trade_type>
		</xml>");
		$this->wechatPay->setHttpClient($this->httpClient);
		$result = $this->wechatPay->getPrepayIdAPP(1,1,1,1);
		$this->assertEquals('wx201411101639507cbf6ffd8b0779950874',$result);
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
		$this->wechatPay->getPrepayId("", "", 1, 'openid', 'ext');
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
		$this->wechatPay->getPrepayId("", "", 1, 'openid', 'ext');
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
		$this->wechatPay->getPrepayId("", "", 1, 'openid', 'ext');
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
