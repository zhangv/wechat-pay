<?php
/**
 * User: derekzhangv
 * Time: 2018/5/29 16:38
 */
namespace zhangv\wechat\cache;

interface CacheProvider{
	function set($key,$value,$expireAt);
	function get($key);
	function clear($key);
}