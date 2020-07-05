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
 * 图片上传
 * Class Image
 * @package App\Lib\Upload
 */
class Image extends UploadBase
{
    /**
     * 文件类型
     * @var string
     */
    public $fileType = 'image';

    /**
     * 文件大小 10M
     * @var int
     */
    public $fileMaxSize = 10485760;

    /**
     * 文件后缀的mediaType
     * @var string[]
     */
    public $fileExtTypes = [
        'png',
        'jpg',
        'jpeg',
        // todo
    ];
}