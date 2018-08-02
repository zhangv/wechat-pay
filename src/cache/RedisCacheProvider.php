<?php
/**
 * RedisCacheProvider
 *
 * @license MIT
 * @author zhangv
 */
namespace zhangv\wechat\pay\cache;
class RedisCacheProvider implements CacheProvider{
	/** @var Redis */
	private $redis = null;

	public function __construct($redis = null){
		$this->redis = $redis;
	}

	public function set($key,$jsonobj,$expireAt){
		$data = $jsonobj;
		$data->expires_at = $expireAt;
		$this->redis->set($key, json_encode($data));
	}

	public function get($key){
		return $this->redis->get($key);
	}

	public function clear($key){
		$this->redis->delete($key);
	}
}