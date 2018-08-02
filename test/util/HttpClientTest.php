<?php
use zhangv\wechat\pay\util\HttpClient;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase{
	private $endPoint = "http://httpbin.org";
	public function testGet(){
		$c = new HttpClient(2);
		$c->get("{$this->endPoint}/get");
		$info = $c->getInfo();
		$this->assertEquals(200,$info['http_code']);
	}

	public function testPost(){
		$c = new HttpClient(2);
		$c->post("{$this->endPoint}/post");
		$info = $c->getInfo();
		$this->assertEquals(200,$info['http_code']);
	}

	public function testBadPost(){
		$c = new HttpClient(2);
		$r = $c->get("{$this->endPoint}/anything");//bad
		$info = $c->getInfo();
		$this->assertEquals(200,$info['http_code']);
		$r = json_decode($r);
		$this->assertNotEquals('POST',$r->method);
	}

}
