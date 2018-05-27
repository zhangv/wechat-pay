<?php
use \PHPUnit\Framework\TestSuite;

class AllTests extends TestSuite {

	public static function suite() {
		$suite = new TestSuite();
		$tests = ['HttpClient','WechatPayMock','WechatOAuth'];
		foreach($tests as $t){
			$filePath = __DIR__ . "/{$t}Test.php";
			require_once($filePath);
			$suite->addTestSuite($t . 'Test');
		}
		return $suite;
	}
}