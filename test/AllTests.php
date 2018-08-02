<?php
use \PHPUnit\Framework\TestSuite;

class AllTests extends TestSuite {

	public static function suite() {
		$suite = new TestSuite();
		$tests = ['WechatPayMockTest','service','cache','util'];
		foreach($tests as $t){
			$path = __DIR__ . '/'.$t;
			if(is_dir($path)){
				self::addDir($path,$suite);
			}else{
				$suite->addTestFile("{$path}.php");
			}
		}
		return $suite;
	}

	private static function addDir($dir,&$suite){
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if($file == '.' || $file == '..') continue;
				$path = $dir . '/' . $file;
				if(is_dir($path)){
					self::addDir($path,$suite);
				}else{
					$suite->addTestFile($path);
				}
			}
			closedir($dh);
		}
	}
}