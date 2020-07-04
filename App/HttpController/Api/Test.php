<?php
/**
 * @filename Index.php
 * @desc this is file description
 * @date 2020/6/28 17:00
 * @author: wsr
 */

namespace App\HttpController\Api;


use App\HttpController\Api\ApiBase;
use App\Lib\AliyunSdk\AliVod;
use App\Model\Es\EsVideo;
use App\Model\User\UserModel;
use App\Model\VideoModel;
use EasySwoole\Component\Di;
use EasySwoole\Http\Message\Status;
use Elasticsearch\ClientBuilder;
use EasySwoole\RedisPool\Redis as PoolRedis;

class Test extends ApiBase
{
    public $logType = 'index';
    // "elasticsearch-php"
    public function esphp()
    {
        // 精确搜索
        $params1 = [
            'index' => 'video-test',
            'type' =>'test',
            'id' => 1
        ];

        $params = [
            'index' => 'video-test',
            'type' =>'test',
            'body' => [
                'query' => [
                    'match' => [
                        'name' => '刘德华'
                    ]
                ]
            ]
        ];

        $client = Di::getInstance()->get('ES');
        $result = $client->get($params1);
       // $result = $client->search($params);
        return $this->writeJson(200, 'OK', $result);
    }

    public function esphp2()
    {
        // 精确搜索
        $params1 = [
            'index' => 'video-test',
            'type' =>'test',
            'id' => 1
        ];

        $params = [
            'index' => 'video-test',
            'type' =>'test',
            'body' => [
                'query' => [
                    'match' => [
                        'name' => '刘德华'
                    ]
                ]
            ]
        ];

        $id_result = (new EsVideo())->searchById(1);
        $match_result = (new EsVideo())->searchByName('刘德华');

        return $this->writeJson(200, 'OK', ['id' => $id_result, 'ma' =>$match_result]);
    }

    public function video()
    {
        $data = [
            'id' => 1,
            'class' => __CLASS__,
            'method' => __METHOD__,
            'time' => date('Y-m-d H:i:s'),
            'url' => 'api/video',
            'param' => $this->request()->getRequestParam()
        ];
        return $this->writeJson(200, 'OK', $data);
    }

    public function users()
    {
        $page = 1;
        $limit = 20;
        $model = new UserModel();
        $data = $model->getUserList($page, '', $limit);
        $this->writeJson(200, $data, 'SUCCESS');
    }

    public function rediss()
    {
        $result = Di::getInstance()->get('REDIS')->get('name');
        $data = ['id' => $result];
        $this->writeJson(200, $data, 'SUCCESS');
    }

    public function zincrby()
    {
        $result = Di::getInstance()->get('REDIS')->zincrby('test_zincrby', 1, 2);
        $data = ['id' => $result];
        $this->writeJson(200, $data, 'SUCCESS');
    }

    public function yaconf()
    {
        $res = \Yaconf::get('redis');
        $this->writeJson(200, 'SUCCESS', $res);
    }

    public function pub()
    {
        $param = $this->request()->getRequestParam();
        Di::getInstance()->get("REDIS")->rPush('consumer_test_list', $param['f']);
    }

    public function testalivod()
    {
        $vod = new AliVod();
        $title = 'upload-vod-test-new';
        $fileName = 'test-new.mp4';
        $result = $vod->createUploadVideo($title, $fileName);

        $videoId = $result->VideoId;
        $uploadAddress = json_decode(base64_decode($result->UploadAddress), true);

        $uploadAuth = json_decode(base64_decode($result->UploadAuth), true);

        $vod->initOssClient($uploadAuth, $uploadAddress);

        $localFile = EASYSWOOLE_ROOT . '/public/upload/video/20200630/8e63932f2f858924.mp4';
        $result = $vod->uploadLocalFile($uploadAddress, $localFile);
        printf("Succeed, VideoId: %s", $videoId);
        print_r($result);
    }

    public function alivoddetail()
    {
        $vod = new AliVod();
        $videoId = '76f0282be564460eadef3da35d00e0b5';
        $result = $vod->getPlayInfo($videoId);
        print_r($result);
    }

    public function redisss()
    {

            //defer方式获取连接
            $redis = PoolRedis::defer('redis');
            $redis->set('test1111', '222');
            $result = $redis->get('test1111');
            var_dump($result);





//
//            //获取连接池对象
//            $redisPool = \EasySwoole\RedisPool\Redis::getInstance()->get('redis');
//            $redisClusterPool = \EasySwoole\RedisPool\Redis::getInstance()->get('redisCluster');
//
//            $redis = $redisPool->getObj();
//            $redisPool->recycleObj($redis);
//
////清除pool中的定时器
//            \EasySwoole\Component\Timer::getInstance()->clearAll();


        return $this->writeJson(200, 'ok', ['defer' => $result,'invoke' => $result1]);
    }
}