<?php
/**
 * User: derekzhangv
 * Time: 2018/6/22 17:25
 */

use zhangv\wechat\cache\RedisCacheProvider;
use PHPUnit\Framework\TestCase;

class RedisCacheProviderTest extends TestCase{
	private $cp = null;
	private $redisMock = null;
	public function setUp(){
		$this->redisMock = $mock = $this->getMockBuilder(stdClass::class)
			->setMethods(['set','get','delete'])
			->getMock();
	}
	public function testClear(){
		$this->redisMock->method('get')->willReturn(null);
		$cp = new RedisCacheProvider($this->redisMock);
		$cp->clear('test');
		$r = $cp->get('test');
		$this->assertEquals(null,$r);
	}

	public function testSet(){
		$jsonobj = new stdClass();
		$jsonobj->a = 'a';
		$this->redisMock->method('get')->willReturn($jsonobj);
		$cp = new RedisCacheProvider($this->redisMock);
		$cp->set('test',$jsonobj,time()+10);
		$r = $cp->get('test');
		$this->assertEquals('a',$r->a);

	}
}
