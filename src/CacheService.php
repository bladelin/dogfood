<?php
namespace Tripresso\Utilities;
/**
 * Cache Service Provider 
 * 
 * @Depdendce CI Framework
 * @author Jun Lin <xuanjunlin@gmail.com>
 */
class CacheService
{
    const TTL = 86400;
    private $redis_db;
    private $drive = 'redis';
    public $CI;
    public $isConn = true;

    public $db;

    public function init(&$CI, $redis_db = 1)
    {
        $this->CI = $CI;
        $this->CI->load->driver('cache', array('adapter' => $this->drive));

        if (!$CI->cache->redis->is_supported() || !$CI->cache->redis->isConn) {
            $this->isConn = false;
        }


        if ($redis_db !=1) {
            $this->redis_db = $redis_db;
        } else {
            if ($this->CI->config->load('redis', TRUE, TRUE))
            {
                $config = $this->CI->config->item('redis');
                $redis_db = $config['redis_default']['database'];
            }
        }
        $this->redis_db = $redis_db;
    }

    public function cleanByKey($cacheKey)
    {
        if ($this->CI->cache->redis->is_supported() || $this->isConn) {
            $this->CI->cache->redis->delete($cacheKey, $this->redis_db);
        }
    }
    /**
     * MethodCached
     *
     * @param  [string]   $cacheKey
     * @param  [function] $method
     * @param  [array]    $args
     * @param  [integer]  $ttl
     * @return [array]
     */
    public function run($cacheKey, $doing, $args = array(),  $ttl = self::TTL)
    {
        $res = array();

        if ($ttl == -1 || strpos(ENVIRONMENT, 'production') === false) {
            $this->cleanByKey($cacheKey);
        }

        if ($this->CI->cache->redis->is_supported() && $this->isConn) {
            $data = $this->CI->cache->redis->get($cacheKey, $this->redis_db);

            if ($data) {
                $res = $data;
            } else {
                $res = $doing($args);
                $this->CI->cache->redis->save($cacheKey, $res, $ttl, false, $this->redis_db);
            }
        } else {
            $res = $doing($args);
        }
        return $res;
    }

    public function get($cacheKey)
    {
        if ($val = $this->CI->cache->redis->get($cacheKey, $this->redis_db)) {
            return $val;
        }
        return false;
    }

    public function save($cacheKey, $res, $ttl = self::TTL)
    {
        if ($this->CI->cache->redis->is_supported()) {
            $this->CI->cache->redis->save($cacheKey, $res, $ttl, false, $this->redis_db);
            return true;
        }
        return false;

    }

    public function getCnt($cacheKey)
    {
        if (!$this->CI->cache->redis->is_supported()) {
            return false;
        }

        if ($val = $this->CI->cache->redis->simpleGet($cacheKey, $this->redis_db)) {
            return $val;
        }
        return false;
    }

    public function incryCnt($cacheKey, $offset = 1)
    {
        if ($this->CI->cache->redis->is_supported()) {
            $this->CI->cache->redis->increment($cacheKey, $offset);
            return $this->getCnt($cacheKey);
        }
        return false;
    }

    public function getByKeys($cacheKey)
    {
        if ($val = $this->CI->cache->redis->getByKeys($cacheKey, $this->redis_db)) {
            return $val;
        }
        return false;
    }

}

