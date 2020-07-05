<?php
/**
 * @filename File.php
 * @desc this is file description
 * @date 2020/7/2 11:15
 * @author: wsr
 */

namespace App\Lib\Caches\Base;
use App\Lib\Caches\CacheBase;

class File extends CacheBase
{
    /**
     * 文件类型
     * @var string
     */
    public $cacheType = 'file';

    /**
     * 获取文件缓存
     * @param string $module
     * @param string $index
     * @return array|mixed
     */
    public function getFileCache($module ='default', $index = 'index')
    {
        $dir = $this->getCacheDir($module);
        $fileName = $dir . '/' . $index . '.json';
        $fileData = is_file($fileName) ? file_get_contents($fileName) : [];
        return !empty($fileData) ? json_decode($fileData, true) : [];
    }

    /**
     * 设置文件缓存
     * @param string $module
     * @param string $index
     * @param $data
     * @return false|int
     */
    public function setFileCache($module ='default', $index = 'index', $data)
    {
        $data = is_array($data) ? $data : [$data];
        $dir = $this->getCacheDir($module);
        $fileName = $dir . '/' . $index . '.json';
        return file_put_contents($fileName, json_encode($data));
    }


    /**
     * @param string $module
     * @return string
     */
    public function getCacheDir($module='default')
    {
        $dirConfig = \Yaconf::get('app.cacheDir');
        $type = $module ?? 'default';
        $cacheDir = isset($dirConfig[$type]) ? $dirConfig[$type] : 'public/cache/default';
        $cacheDir = EASYSWOOLE_ROOT . '/'.$cacheDir;
        if (!is_dir($cacheDir)) {
            \EasySwoole\Utility\File::createDirectory($cacheDir);
        }

        return $cacheDir;
    }

}