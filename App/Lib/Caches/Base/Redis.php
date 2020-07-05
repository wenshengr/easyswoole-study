<?php
/**
 * @filename Redis.php
 * @desc this is file description
 * @date 2020/7/2 11:15
 * @author: wsr
 */

namespace App\Lib\Caches\Base;


use App\Lib\Caches\CacheBase;
use EasySwoole\Component\Di;

class Redis extends CacheBase
{
    /**
     * 文件类型
     * @var string
     */
    public $cacheType = 'redis';

    /**
     * 设置Redis缓存
     * @param $module
     * @param $key
     * @param $data
     * @return string
     * @throws \Throwable
     */
    public function setRedisCache($module, $key, $data)
    {
        if (empty($module) || empty($key)) {
            return '';
        }
        $key = $module.'_'.$key;
        $key = str_replace('_',':',$key);
        $val = is_array($data) ? json_encode($data) : $data;
        return Di::getInstance()->get('REDIS')->set($key, $val);
    }


    /**
     * 读取redis缓存
     * @param $module
     * @param $key
     * @return string
     * @throws \Throwable
     */
    public function getRedisCache($module, $key)
    {
        if (empty($module) || empty($key)) {
            return '';
        }
        $key = $module.'_'.$key;
        $key = str_replace('_',':',$key);
        $data = Di::getInstance()->get('REDIS')->get($key);
        return !empty($data) ? json_decode($data, true) : [];
    }
}