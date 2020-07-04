<?php
/**
 * @filename Index.php
 * @desc this is file description
 * @date 2020/6/28 17:00
 * @author: wsr
 */

namespace App\HttpController\Api;


use App\HttpController\Api\ApiBase;
use App\Model\VideoModel;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Message\Status;
use App\Lib\Caches\Video as VideoCache;

class Index extends ApiBase
{
    public $logType = 'index';

    public function search()
    {

    }

    /**
     * 排行接口 总排行、日排行、月排行、周排行
     */
    public function rank()
    {
        $result = Di::getInstance()->get('REDIS')->zrevrange('video_play', 0, -1);
        return $this->writeJson(STATUS::CODE_OK, 'OK', $result);
    }

    public function index()
    {
        $id = !empty($this->params['id']) ? intval($this->params['id']) : 0;
        if (!$id) {
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, '请求不合法');
        }

        try {
            $video = (new VideoModel())->getDataById($id);
        } catch (\Exception $e) {
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, '请求不合法');
        }

        if (!$video || $video['status'] != VideoModel::STATUS_AUDITED) {
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, '视频不存在');
        }

        $video['video_duration'] = gmstrftime('%H:%M:%S', $video['video_duration']);

        // 播放数统计逻辑
        // 投放task任务
        $lof = $this->logType;
        // 127.0.0.1:6379> ZRANGE video_play 0 -1 withscores
        TaskManager::getInstance()->async(function () use($id, $lof) {
            // 总排行
            $result = Di::getInstance()->get('REDIS')->zincrby('video_play', 1, $id);
            if (!$result) {
                Logger::getInstance()->log("{$lof}|index-video_play:$result");
            }

            // 日排行记录
            $result = Di::getInstance()->get('REDIS')->zincrby('video_play_'.date('Ymd'), 1, $id);
        });
        return $this->writeJson(STATUS::CODE_OK, "OK", $video);
    }

    /**
     * 点赞数
     * @return bool
     * @throws \Throwable
     */
    public function love()
    {
        $id = !empty($this->params['id']) ? intval($this->params['id']) : 0;
        if (!$id) {
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, '请求不合法');
        }

        try {
            $video = (new VideoModel())->getDataById($id);
        } catch (\Exception $e) {
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, '请求不合法');
        }

        if (!$video || $video['status'] != VideoModel::STATUS_AUDITED) {
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, '视频不存在');
        }

        $video['video_duration'] = gmstrftime('%H:%M:%S', $video['video_duration']);

        $result = Di::getInstance()->get('REDIS')->zincrby('video_play_love', 1, $id);

        // 记录视频与用户的对应关系

        return $this->writeJson(STATUS::CODE_OK, "OK", $video);
    }

    /**
     * 第一套方案 - 原始读取MYSQL
     * @return bool
     */
    public function lists()
    {
        $condition = [];
        if (!empty($this->params['cat_id'])) {
            $condition['cat_id'] = intval($this->params['cat_id']);
        }

        try {
            $model = new VideoModel();
            $result = $model->getVideoList($condition, $this->params['page'], $this->params['pageSize']);
        } catch (\Exception $e) {
            $this->writeLog(sprintf("%s|%s:%s", $this->logType, 'list', $e->getMessage()));
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, "服务异常");
        }

        if (!empty($result['list'])) {
            foreach ($result['list'] as &$list) {
                $list['create_time'] = date('Y-m-d H:i:s', $list['create_time']);
                $list['video_duration'] = gmstrftime('%H:%M:%S', $list['video_duration']);
            }
        }

        return $this->writeJson(STATUS::CODE_BAD_REQUEST, 'OK', $result);
    }

    /**
     * 第二套方案 - 直接读取静态化json数据
     * @return bool
     */
    public function list2()
    {
        $catId = !empty($this->params['cat_id']) ? intval($this->params['cat_id']) : 0;
        try {
            $videoData = (new VideoCache())->getVideoCache($catId);
        } catch (\Exception $e) {
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, '请求失败');
        }

        $count = count($videoData);
        $data = $this->getPagingDatas($count, $videoData);
        return $this->writeJson(STATUS::CODE_OK, 'OK', $data);
    }

}