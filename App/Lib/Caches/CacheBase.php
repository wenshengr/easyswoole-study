<?php
/**
 * @filename CacheBase.php
 * @desc this is file description
 * @date 2020/7/2 11:17
 * @author: wsr
 */

namespace App\Lib\Caches;


use App\Lib\Untils;

class CacheBase
{
    /**
     * 缓存文件类型
     * @var string
     */
    public $type = '';


    /**
     * CacheBase constructor.
     * @param $request
     * @param null $type
     */
    public function __construct($request, $type = null)
    {
        $this->request = $request;
        if (!$type) {
            $this->type = \Yaconf::get('app.indexCacheType');
        } else {
            $this->type = $type;
        }
    }

    public function getCache()
    {

    }

    public function setCache()
    {
        if ($this->type != $this->cacheType) {
            return false;
        }

        $video = $this->request->getUploadedFile($this->type);
        $this->size = $video->getSize();
        $this->checkSize();

        $this->clientMediaType = $video->getClientMediaType();
        $this->checkClientMediaType();

        $fileName = $video->getClientFileName();

        $file = $this->getFile($fileName);
        $flag = $video->moveTo($file);
        if (!empty($flag)) {
            return $this->file;
        }
        return FALSE;
    }



    /**
     * 获取文件信息
     * @param $fileName
     * @return string
     */
    public function getFile($fileName)
    {
        $fileInfo = pathinfo($fileName);
        $extension = $fileInfo['extension'];
        $uploadDir = '/upload/'.$this->type.'/'.date('Ymd');
        $dir = EASYSWOOLE_ROOT . '/public'. $uploadDir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $baseFileName = Untils::getFileFrontKey($fileName). '.'. $extension;
        $this->file = $uploadDir.'/'.$baseFileName;
        return $dir.'/'.$baseFileName;
    }

}