<?php
/**
 * @filename AliVod.php
 * @desc this is file description
 * @date 2020/7/1 9:56
 * @author: wsr
 */

namespace App\Lib\AliyunSdk;

require_once EASYSWOOLE_ROOT.'/App/Lib/AliyunSdk/aliyun-php-sdk-core/Config.php';
require_once EASYSWOOLE_ROOT.'/App/Lib/AliyunSdk/aliyun-oss-php-sdk/autoload.php';
use vod\Request\V20170321 as vod;
use OSS\OssClient;
use OSS\Core\OssException;

class AliVod
{
    public $regionId = 'cn-shanghai';

    public $vodClient = null;

    public $ossClient = null;

    // 使用AK初始化VOD客户端
    public function __construct()
    {
        $profile = \DefaultProfile::getProfile($this->regionId, \Yaconf::get('aliyun.accessKeyId'), \Yaconf::get('aliyun.accessKeySecret'));
        $this->vodClient = new \DefaultAcsClient($profile);
    }

    /**
     * 获取视频上传地址和凭证
     * @param $title
     * @param $fileName
     * @param array $other
     * @return mixed|\SimpleXMLElement
     * @throws \ClientException
     * @throws \ServerException
     */
    public function createUploadVideo($title, $fileName, $other=[])
    {
        $request = new vod\CreateUploadVideoRequest();
        $request->setTitle($title);        // 视频标题(必填参数)
        $request->setFileName($fileName); // 视频源文件名称，必须包含扩展名(必填参数)
        if (!empty($other['desc'])) {
            $request->setDescription($other['desc']);  // 视频源文件描述(可选)
        }
        if (!empty($other['coverURL'])) {
            $request->setCoverURL($other['coverURL']); // 自定义视频封面(可选)
        }
        if (!empty($other['tags'])) {
            $tags = is_array($other['tags']) ? $other['tags'] : [$other['tags']];
            $tags = implode(",", $tags);
            $request->setTags($tags); // 视频标签，多个用逗号分隔(可选)
        }
        $response = $this->vodClient->getAcsResponse($request);
        if (empty($response) || empty($response->VideoId)) {
            throw new \Exception('获取上传凭证不合法!');
        }
        return $response;
    }

    // 使用上传凭证和地址初始化OSS客户端（注意需要先Base64解码并Json Decode再传入）
    public function initOssClient($uploadAuth, $uploadAddress)
    {
        $this->ossClient = new OssClient($uploadAuth['AccessKeyId'], $uploadAuth['AccessKeySecret'], $uploadAddress['Endpoint'],
            false, $uploadAuth['SecurityToken']);
        $this->ossClient->setTimeout(\Yaconf::get('aliyun.timeOut'));
        $this->ossClient->setConnectTimeout(\Yaconf::get('aliyun.connectTimeout'));
        return $this->ossClient;
    }

    // 上传本地文件
    public function uploadLocalFile($uploadAddress, $localFile)
    {
        return $this->ossClient->uploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
    }

    // 刷新上传凭证
    public function refresh_upload_video($vodClient, $videoId)
    {
        $request = new vod\RefreshUploadVideoRequest();
        $request->setVideoId($videoId);
        return $vodClient->getAcsResponse($request);
    }

    public function getPlayInfo($videoId)
    {
        if (empty($videoId)) {
            return [];
        }
        $request = new vod\GetPlayInfoRequest();
        $request->setVideoId($videoId);
        $request->setAcceptFormat('JSON');
        return $this->vodClient->getAcsResponse($request);
    }
}