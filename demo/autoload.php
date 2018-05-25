<?php

if(file_exists(__DIR__ . '/../vendor/autoload.php')){
	/** @var \Composer\Autoload\ClassLoader $loader */
	$loader = require __DIR__ . '/../vendor/autoload.php';
}else{//if the composer is not initialized, note: this is not work in phpunit, switch to composer when unit testing
	function __autoload($class) {
		$class = str_replace('zhangv\\wechat\\', 'src/', $class);
		$file = __DIR__ . '/../' . $class . '.php';
		require_once $file;
	}
	spl_autoload_register('__autoload');
}
