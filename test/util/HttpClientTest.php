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

	public function testTimeout(){
		$c = new HttpClient(0.01,2);
		$r = $c->get("{$this->endPoint}/delay/1");//timeout
		$this->assertEquals(28,$c->getErrorNo());
		$this->assertEquals(3,$c->getTried());

		$c = new HttpClient(2,2);
		$r = $c->get("{$this->endPoint}/delay/1");//NOT timeout
		$this->assertEquals(0,$c->getErrorNo());
		$this->assertEquals(1,$c->getTried());
	}
}
