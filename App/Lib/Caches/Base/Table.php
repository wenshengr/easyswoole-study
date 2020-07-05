<?php
/**
 * @filename Table.php
 * @desc this is file description
 * @date 2020/7/2 11:15
 * @author: wsr
 */

namespace App\Lib\Caches\Base;


use App\Lib\Caches\CacheBase;
use EasySwoole\FastCache\Cache;

class Table extends CacheBase
{
    /**
     * 文件类型
     * @var string
     */
    public $cacheType = 'table';

    /**
     * 设置fast-cache缓存
     * @param $module
     * @param $key
     * @param $data
     * @return bool|mixed|string|null
     */
    public function setTableCache($module, $key, $data)
    {
        if (empty($module) || empty($key)) {
            return '';
        }
        $key = $module.'_'.$key;
        $key = str_replace('_',':',$key);
        $val = is_array($data) ? $data : [$data];
        return Cache::getInstance()->set($key, $val);
    }

    /**
     * 获取easyswoole cache缓存
     * @param $module
     * @param $key
     * @return array|mixed|string
     */
    public function getTableCache($module, $key)
    {
        if (empty($module) || empty($key)) {
            return '';
        }
        $key = $module.'_'.$key;
        $key = str_replace('_',':',$key);
        $cache = Cache::getInstance()->get($key);
        return !empty($cache) ? $cache : [];
    }
}