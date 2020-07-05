<?php
/**
 * @filename Base.php
 * @desc this is file description
 * @date 2020/6/30 13:54
 * @author: wsr
 */

namespace App\Lib\Upload;
use App\Lib\Untils;

class UploadBase
{
    /**
     * 上传文件的 file - key
     * @var string
     */
    public $type = '';

    /**
     * @var
     */
    private $clientMediaType;

    /**
     * Base constructor.
     * @param $request
     */
    public function __construct($request, $type  = null)
    {
        $this->request = $request;
        if (!$type) {
            $files = $this->request->getSwooleRequest()->files;
            $types = array_keys($files);
            $this->type = $types[0];
        } else {
            $this->type = $type;
        }

    }

    public function upload()
    {
        if ($this->type != $this->fileType) {
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

    /**
     * 检测上传文件大小
     * @return bool
     */
    public function checkSize(  )
    {
        if (!$this->size) {
            throw new \Exception("请上传文件");
        }
        if ($this->size > $this->fileMaxSize) {
            throw new \Exception("上传的文件太大了");
        }
        return TRUE;
    }

    /**
     * 检测文件类型
     * @return bool
     * @throws \Exception
     */
    public function checkClientMediaType()
    {
        $clientMediaType = explode('/', $this->clientMediaType);
        $clientMediaType = $clientMediaType[1] ?? '';
        if (!$clientMediaType){
            throw new \Exception("上传的{$this->type}文件不合法");
        }
        if (!in_array($clientMediaType, $this->fileExtTypes)) {
            throw new \Exception("上传的{$this->type}文件不合法");
        }
        return TRUE;
    }
}