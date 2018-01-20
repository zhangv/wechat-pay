<?php
/**
 * @license MIT
 * @author zhangv
 */
namespace zhangv\wechat;
use zhangv\wechat\HttpClient;

class WechatOAuth {

	public $responseJSON = null;
	public $errCode = null;
	public $errMsg = null;

	private $appId,$appSecret;
	private $redirectURI = null;
	private $httpClient = null;
	private $accessToken = null;

	public function __construct($appId,$appSecret,$redirectURI = null) {
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		$this->redirectURI = $redirectURI;
		$this->httpClient = new HttpClient();
	}

	public function setHttpClient($httpClient){
		$this->httpClient = $httpClient;
	}

	public function setAccessToken($accessToken){
		$this->accessToken = $accessToken;
	}

	public function authorizeURI($scope = 'snsapi_userinfo',$state = ''){
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$this->redirectURI}&response_type=code&scope=$scope&state=$state#wechat_redirect";
	}

	public function authorize($code){
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code=$code&grant_type=authorization_code";
		$this->responseJSON = $this->httpClient->get($url);
		return json_decode($this->responseJSON);
	}

	public function getUserInfo($openid){
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token={$this->accesstoken}&openid=$openid&lang=zh_CN";
		$this->responseJSON = $this->httpClient->get($url);
		return json_decode($this->responseJSON);
	}

	public function refreshToken($refreshToken){
		$url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$this->appId}&grant_type=refresh_token&refresh_token=$refreshToken";
		$this->responseJSON = $this->httpClient->get($url);
		return $this->responseJSON;
	}

	public function verifyToken($accessToekn,$openId){
		$url = "https://api.weixin.qq.com/sns/auth?access_token=$accessToekn&openid=$openId";
		$this->responseJSON = $this->httpClient->get($url);
		return $this->responseJSON;
	}

}
