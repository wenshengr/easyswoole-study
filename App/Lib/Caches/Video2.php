<?php
/**
 * @filename Video.php
 * @desc this is file description
 * @date 2020/7/1 17:24
 * @author: wsr
 */

namespace App\Lib\Caches;

use App\Lib\ClassReflect;
use App\Model\VideoModel;


class Video2
{
    /**
     * 设置video首页缓存
     * @throws \Throwable
     */
    public function setIndexVideo()
    {
        $cacheType = \Yaconf::get('app.indexCacheType');
        $cacheTypeList = \Yaconf::get('app.cacheTypeList');
        $cacheTypeList = array_values($cacheTypeList);
        if (!in_array($cacheType, $cacheTypeList)) {
            throw new \Exception("无效的配置信息!");
        }

        $classObj = new ClassReflect();
        $classStats = $classObj->cacheClassStat();

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

            $params['module'] = 'video';
            $params['index'] = 'index';
            $params['extra'] = $catId;

            try {
                $ctype = 'Cache'.ucfirst($cacheType);
                $cacheClass = $classObj->initClass($ctype, $classStats, [$params, $cacheType, $data]);
                $result = $cacheClass->setCache();
            } catch (\Exception $e) {
                throw new \Exception("设置缓存失败!");// $e->getMessage()
            }

            if (!$result) {
                // todo 报警处理
                echo "cache:" . implode('_',$params) . " put data error!" . PHP_EOL;
            } else {
                echo "cache:" . implode('_',$params) . " put data success!" . PHP_EOL;
            }
        }

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

        $params['module'] = 'video';
        $params['index'] = 'index';
        $params['extra'] = $catId;

        $ctype = 'Cache'.ucfirst($cacheType);

        $classObj = new ClassReflect();
        $classStats = $classObj->cacheClassStat();
        $cacheClass = $classObj->initClass($ctype, $classStats, [$params, $cacheType, []]);
        $result = $cacheClass->getCache();
        if (!$result) {
            // todo 暂无缓存 报警邮件
            echo "catId:" . $catId . " no cache!" . PHP_EOL;
        }
        return $result;
    }
}