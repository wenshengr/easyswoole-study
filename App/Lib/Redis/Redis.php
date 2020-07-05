<?php
/**
 * @filename Redis.php
 * @desc this is file description
 * @date 2020/6/29 11:03
 * @author: wsr
 */

namespace App\Lib\Redis;


use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;

class Redis
{
    use Singleton;

    public $redis = null;

    private function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \Exception("redis.so 文件不存在！");
        }
        try {
            //$config = Config::getInstance()->getConf('redis.REDIS');
            $config = \Yaconf::get('redis');
            $this->redis = new \Redis();
            $result = $this->redis->connect($config['host'], $config['port'], $config['timeout']);
            $this->redis->auth($config['auth']);
        } catch (\Exception $e) {
            throw new \Exception("Redis 服务异常！");
        }

        if ($result === false) {
            throw new \Exception("Redis 连接失败！");
        }
    }

    /**
     * @param $key
     * @return bool|mixed|string
     */
    public function get($key)
    {
        if (!$key) {
            return '';
        }
        return $this->redis->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key, $value, $time = 0)
    {
        if (!$key) {
            return '';
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        if (!$time) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $time, $value);
    }

    /**
     * @param $key
     * @return bool|mixed|string
     */
    public function lPop($key)
    {
        if (!$key) {
            return '';
        }
        return $this->redis->lPop($key);
    }

    /**
     * @param $key
     * @param $value
     * @return bool|int|string
     */
    public function rPush($key, $value)
    {
        if (!$key) {
            return '';
        }
        return $this->redis->rPush($key, $value);
    }

    /**
     * @param $key
     * @param $number
     * @param $member
     * @return bool
     */
    public function zincrby($key, $number, $member)
    {
        if (!$key || !$member) {
            return false;
        }

        return $this->redis->zIncrBy($key, $number, $member);
    }

    /**
     * @param $key
     * @param $start
     * @param $top
     * @param bool $withscore
     * @return array|bool
     */
    public function zrevrange($key, $start, $top, $withscore = true)
    {
        if (!$key) {
            return false;
        }

        return $this->redis->zRevRange($key, $start, $top, $withscore);
    }

    /**
     * 当类中的方法不存在时，直接调用call方法，来实现调用redis底层的方法
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        // ... 表示可变长度参数
        $this->redis->$name(...$arguments);
    }
}