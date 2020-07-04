<?php
/**
 * @filename Upload.php
 * @desc this is file description
 * @date 2020/6/29 17:00
 * @author: wsr
 */

namespace App\HttpController\Api;

use App\HttpController\Api\ApiBase;
use App\Lib\ClassReflect;
use App\Lib\Upload\Video;
use App\Model\User\UserModel;
use EasySwoole\Component\Di;

/**
 * 文件上传逻辑
 * Class Upload
 * @package App\HttpController\Api
 */
class Upload extends ApiBase
{
    public function file()
    {
        $request = $this->request();
        $files = $request->getSwooleRequest()->files;
        $types = array_keys($files);
        $type = $types[0];
        if (!$type) {
            return $this->writeJson(30001, '上传的文件不合法');
        }
        try {
            $classObj = new ClassReflect();
            $classStats = $classObj->uploadClassStat();
            $uploadClass = $classObj->initClass($type, $classStats, [$request, $type]);
            $file = $uploadClass->upload();
        } catch (\Exception $e) {
            return $this->writeJson(30000, $e->getMessage());
        }

        if (!$file) {
            return $this->writeJson(30001, '视频上传失败');
        }
        $data = [
            'url' => $file,
        ];
        return $this->writeJson(200, "SUCCESS", $data);
    }

}