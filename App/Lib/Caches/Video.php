<?php
/**
 * @filename Video.php
 * @desc this is file description
 * @date 2020/7/1 17:24
 * @author: wsr
 */

namespace App\Lib\Caches;

use App\Model\VideoModel;
use EasySwoole\Component\Di;
use EasySwoole\FastCache\Cache;
use EasySwoole\Utility\File;

class Video
{
    public function setIndexVideo()
    {
        $cacheType = \Yaconf::get('app.indexCacheType');
        $catIds = \Yaconf::get('category.cats');
        $catIds = array_keys($catIds);
        array_unshift($catIds, 0);

        $model = new VideoModel();
        foreach ($catIds as $catId) {
            $where = [];
            if (!empty($catId)) {
                $where['cat_id'] = $catId;
            }
            try {
                $data = $model->getVideoCacheData($where);
            } catch (\Exception $e) {
                // todo 报警处理
                $data = [];
            }

            if (empty($data)) {
                continue;
            }

            foreach ($data as &$item) {
                $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['video_duration'] = gmstrftime('%H:%M:%S', $item['video_duration']);
            }

            switch ($cacheType) {
                case 'file':
                    // 文件缓存
                    $result = $this->setFileCache($catId, $data);
                    break;
                case 'redis':
                    // redis 缓存
                    $result = $this->setVideoRedisCache($catId, $data);
                    break;
                case 'table':
                    // easyswoole cache 缓存
                    $result = $this->setVideoTableCache($catId, $data);
                    break;
                default:
                    throw new \Exception("请求不合法!");
                    break;
            }

            if (!$result) {
                // todo 报警处理
                echo "catId:" . $catId . " put data error!" . PHP_EOL;
            } else {
                echo "catId:" . $catId . " put data success!" . PHP_EOL;
            }
        }

    }

    /**
     * 获取缓存key
     * @param int $catId
     * @return string
     */
    private function getIndexVideoCacheKey($catId = 0)
    {
        return 'index_vedio_cat_' . $catId;
    }

    /**
     * 获取文件缓存目录
     * @return string
     */
    private function getIndexVideoCacheDir()
    {
        $indexCacheFilePath = \Yaconf::get('app.indexCacheFilePath');
        return EASYSWOOLE_ROOT . '/'.$indexCacheFilePath;
    }

    /**
     * 设置文件缓存
     * @param $catId
     * @param $data
     * @return false|int
     */
    public function setFileCache($catId, $data)
    {
        $dir = $this->getIndexVideoCacheDir();
        File::createDirectory($dir);
        return file_put_contents($dir . '/' . $catId . '.json', json_encode($data));
    }

    /**
     * 设置SwooleTable缓存
     * @param $catId
     * @param $data
     */
    public function setVideoTableCache($catId, $data)
    {
        $key = $this->getIndexVideoCacheKey($catId);
        return Cache::getInstance()->set($key, $data);
    }

    /**
     * 设置Redis缓存缓存
     * @param $catId
     * @param $data
     */
    public function setVideoRedisCache($catId, $data)
    {
        $key = $this->getIndexVideoCacheKey($catId);
        return Di::getInstance()->get('REDIS')->set($key, $data);
    }

    /**
     * 获取easyswoole cache缓存
     * @param $catId
     * @return array|mixed
     */
    public function getVideoTableCache($catId)
    {
        $key = $this->getIndexVideoCacheKey($catId);
        $cache = Cache::getInstance()->get($key);
        $cache = !empty($cache) ? $cache : [];
        return $cache;
    }

    /**
     * 获取文件缓存
     * @param $catId
     * @return array|mixed
     */
    public function getVideoFileCache($catId)
    {
        $videoJsonFile = $this->getIndexVideoCacheDir() . '/' . $catId . '.json';
        $videoData = is_file($videoJsonFile) ? file_get_contents($videoJsonFile) : [];
        $videoData = !empty($videoData) ? json_decode($videoData, true) : [];
        return $videoData;
    }

    /**
     * 获取redis缓存
     * @param $catId
     * @return array|mixed
     */
    public function getVideoRedisCache($catId)
    {
        $key = $this->getIndexVideoCacheKey($catId);
        $cache = Di::getInstance()->get('REDIS')->get($key);
        $cache = !empty($cache) ? json_decode($cache, true) : [];
        return $cache;
    }

    /**
     * 获取缓存
     * @param int $catId
     * @return array|mixed
     * @throws \Exception
     */
    public function getVideoCache($catId = 0)
    {
        $cacheType = \Yaconf::get('app.indexCacheType');
        switch ($cacheType) {
            case 'file':
                // 文件缓存
                $result = $this->getVideoFileCache($catId);
                break;
            case 'redis':
                // redis 缓存
                $result = $this->getVideoRedisCache($catId);
                break;
            case 'table':
                // easyswoole cache 缓存
                $result = $this->getVideoTableCache($catId);
                break;
            default:
                throw new \Exception("请求不合法!");
                break;
        }
        if (!$result) {
            // todo 暂无缓存 报警邮件
            echo "catId:" . $catId . " no cache!" . PHP_EOL;
        }
        return $result;
    }
}