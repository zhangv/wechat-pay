<?php
use \PHPUnit\Framework\TestSuite;

class AllTests extends TestSuite {

	public static function suite() {
		$suite = new TestSuite();
		$tests = ['HttpClient','WechatPayMock','WechatOAuth','cache/RedisCacheProvider','cache/JsonFileCacheProvider'];
		foreach($tests as $t){
			$filePath = __DIR__ . "/{$t}Test.php";
			$suite->addTestFile($filePath);
		}
		return $suite;
	}
}