<?php
/**
 * @filename Redis.php
 * @desc this is file description
 * @date 2020/7/3 23:28
 * @author: wsr
 */

namespace App\Lib\Db;

use EasySwoole\Component\Singleton;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\Redis as PoolRedis;

class Redis
{
    use Singleton;

    /**
     * 初始化redis
     */
    public function initRedis()
    {
        $config =  \Yaconf::get('redis');
        try {
            $redisConfig = new RedisConfig();
            $redisConfig->setHost($config['host']);
            $redisConfig->setPort($config['port']);
            $redisConfig->setAuth($config['auth']);
            $redisConfig->setDb($config['database']);
            $redisConfig->setTimeout($config['timeout']);
            $redis = PoolRedis::getInstance()->register('redis', $redisConfig);

            //配置连接池连接数
            $redis->setMinObjectNum($config['POOL_MIN_NUM']);
            $redis->setMaxObjectNum($config['POOL_MAX_NUM']);
        } catch (\Exception $e) {
            // 注册失败 todo 发邮件
            //throw new Exception('Redis 注册失败！');
            echo "[Warn] --> Redis 注册失败\n";
        }
    }

    /**
     * redis集群连接池注册
     * @throws Exception
     */
    public function initRedisCluster()
    {
        $config = \Yaconf::get('redis.cluster');
        
        $serverList = [];
        foreach ($config as $item) {
            $serverList[] = [
                'host' => $item['host'],
                'port' => $item['port'],
            ];
        }

        try {
            $redisClusterConfig = new \EasySwoole\Redis\Config\RedisClusterConfig($serverList);
            $redisClusterConfig->setAuth($config[0]['auth']);
            $redisClusterConfig->setDb($config[0]['database']);
            PoolRedis::getInstance()->register('redisCluster', $redisClusterConfig);
        } catch (\Exception $e) {
            // 注册失败 todo 发邮件
            //throw new Exception('RedisCluster 注册失败！');
            echo "[Warn] --> RedisCluster 注册失败\n";
        }
    }

    public function __destruct()
    {
        $redisPool = PoolRedis::getInstance()->get('redis');
        $redis = $redisPool->getObj();
        $redisPool->recycleObj($redis);
    }
}