<?php
/**
 * CacheProvider
 *
 * @license MIT
 * @author zhangv
 */
namespace zhangv\wechat\pay\cache;

interface CacheProvider{
	function set($key,$value,$expireAt);
	function get($key);
	function clear($key);
}