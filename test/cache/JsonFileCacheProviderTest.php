<?php

use zhangv\wechat\pay\cache\JsonFileCacheProvider;
use PHPUnit\Framework\TestCase;

class JsonFileCacheProviderTest extends TestCase {
	/** @var JsonFileCacheProvider */
	private $cp = null;
	public function setUp() {
		$this->cp = new JsonFileCacheProvider(__DIR__);
	}

	public function tearDown() {
		$this->cp->clear('testkey');
	}

	public function testClear() {
		$jsonobj = json_decode('{
			"errcode":0,
			"errmsg":"ok",
			"ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA",
			"expires_in":7200
			}');
		$this->cp->set('testkey',$jsonobj,time()+100);
		$r = $this->cp->get('testkey');
		$this->assertEquals('0',$r->errcode);
		$this->cp->clear('testkey');
		$r = $this->cp->get('testkey');
		$this->assertNull($r);
	}

	public function testGet() {
		$jsonobj = json_decode('{
			"errcode":0,
			"errmsg":"ok",
			"ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA",
			"expires_in":7200
			}');
		$this->cp->set('testkey',$jsonobj,time()+100);
		$r = $this->cp->get('testkey');
		$this->assertEquals('0',$r->errcode);
		$this->cp->set('testkey',$jsonobj,time()-100);
		$r = $this->cp->get('testkey');
		$this->assertNull($r);
	}

	public function testSet() {
		$jsonobj = json_decode('{
			"errcode":0,
			"errmsg":"ok",
			"ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKdvsdshFKA",
			"expires_in":7200
			}');
		$this->cp->set('testkey',$jsonobj,time()+100);
		$r = $this->cp->get('testkey');
		$this->assertEquals('0',$r->errcode);
	}
}
