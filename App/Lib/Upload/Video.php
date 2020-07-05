<?php
/**
 * @filename Vedio.php
 * @desc this is file description
 * @date 2020/6/30 13:40
 * @author: wsr
 */

namespace App\Lib\Upload;

use App\Lib\Upload\UploadBase;

/**
 * 视频上传
 * Class Video
 * @package App\Lib\Upload
 */
class Video extends UploadBase
{
    /**
     * 文件类型
     * @var string
     */
    public $fileType = 'video';

    /**
     * 文件大小 100M
     * @var int
     */
    public $fileMaxSize = 104857600;

    /**
     * 文件后缀的mediaType
     * @var string[]
     */
    public $fileExtTypes = [
        'mp4',
        'x-flv',
        // todo
    ];
}