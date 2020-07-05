<?php
/**
 * @filename Search.php
 * @desc this is file description
 * @date 2020/7/3 11:14
 * @author: wsr
 */

namespace App\HttpController\Api;


use App\HttpController\Api\ApiBase;
use App\Model\Es\EsVideo;
use EasySwoole\Http\Message\Status;

class Search extends ApiBase
{
    public function index()
    {
        $keyword = !empty($this->params['keyword']) ? trim($this->params['keyword']) : '';
        if (empty($keyword)) {
            return $this->writeJson(STATUS::CODE_OK, 'OK', []);
        }
        $esObj = new EsVideo();
        $result = $esObj->searchByName($keyword, $this->params['from'], $this->params['pageSize']);
        if (empty($result)) {
            return $this->writeJson(STATUS::CODE_OK, 'OK', []);
        }

        $total = $result['hits']['total']['value'];
        if (empty($total)) {
            return $this->writeJson(STATUS::CODE_OK, 'OK', []);
        }
        $hits = $result['hits']['hits'];
        foreach ($hits as $hit) {
            $source = $hit['_source'];
            $resData[] = [
                'id' => $hit['_id'],
                'name' => $source['name'],
                'cat_id' => $source['cat_id'],
                'image' => $source['image'],
                'url' => $source['url'],
                'type' => $source['type'],
                'uploader' => $source['uploader'],
                'status' => $source['status'],
                'video_id' => $source['video_id'],
            ];
        }
        $this->writeJson(STATUS::CODE_OK, 'Ok', $this->getPagingDatas($total, $resData, 0));
    }
}