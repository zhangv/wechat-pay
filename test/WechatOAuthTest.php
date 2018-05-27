<?php
use zhangv\wechat\HttpClient;
use zhangv\wechat\WechatOAuth;
use PHPUnit\Framework\TestCase;

class WechatOAuthTest extends TestCase{
	/** @var HttpClient */
	private $httpClient;
	/** @var WechatOAuth */
	private $wechatOauth;

	public function setUp(){
		$this->httpClient = $this->createMock(HttpClient::class);
		$this->wechatOauth = new WechatOAuth('appid','appsecret');
	}

	/** @test */
	public function authorizeURI(){
		$uri = $this->wechatOauth->authorizeURI('');
		$this->assertNotNull($uri);
	}

	/** @test */
	public function authorize(){
		$this->httpClient->method('get')->willReturn('{
			"errcode":0,
			"errmsg":"ok"
			}');
		$this->wechatOauth->setHttpClient($this->httpClient);
		$r = $this->wechatOauth->authorize('');
		$this->assertEquals('0',$r->errcode);
	}
	/** @test */
	public function getUserInfo(){
		$this->httpClient->method('get')->willReturn('
		{
			"openid":"OPENID",
			"nickname": "NICKNAME",
			"sex":"1",
			"province":"PROVINCE",
			"city":"CITY",
			"country":"COUNTRY",
			"headimgurl":"http://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
			"privilege":[ "PRIVILEGE1","PRIVILEGE2"],
			"unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
		}');
		$this->wechatOauth->setHttpClient($this->httpClient);
		$r = $this->wechatOauth->getUserInfo('');
		$this->assertEquals('OPENID',$r->openid);
	}
	/** @test */
	public function refreshToken(){
		$this->httpClient->method('get')->willReturn('
		{
			"access_token":"ACCESS_TOKEN",
			"expires_in":7200,
			"refresh_token":"REFRESH_TOKEN",
			"openid":"OPENID",
			"scope":"SCOPE"
		}');
		$this->wechatOauth->setHttpClient($this->httpClient);
		$r = $this->wechatOauth->refreshToken('');
		$this->assertEquals('REFRESH_TOKEN',$r->refresh_token);
	}
	/** @test */
	public function verifyToken(){
		$this->httpClient->method('get')->willReturn('
		{
			"errcode":0,
			"errmsg":"ok"
		}');
		$this->wechatOauth->setHttpClient($this->httpClient);
		$r = $this->wechatOauth->verifyToken(1,1);
		$this->assertEquals('0',$r->errcode);
	}
	/** @test */
	public function getAccessToken(){
		$this->httpClient->method('get')->willReturn('
		{"access_token":"ACCESS_TOKEN","expires_in":7200}');
		$this->wechatOauth->setHttpClient($this->httpClient);
		$r = $this->wechatOauth->getAccessToken();
		$this->assertEquals('ACCESS_TOKEN',$r);
	}
	/** @test */
	public function getTicket(){
		$this->httpClient->method('get')->willReturn('
		{
			"errcode":0,
			"errmsg":"ok",
			"ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITDGgnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA",
			"expires_in":7200
		}');
		$this->wechatOauth->setHttpClient($this->httpClient);
		$r = $this->wechatOauth->getTicket('','token');
		$this->assertEquals('bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITDGgnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA',$r->ticket);
	}

}
