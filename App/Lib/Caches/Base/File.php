<?php
/**
 * @filename File.php
 * @desc this is file description
 * @date 2020/7/2 11:15
 * @author: wsr
 */

namespace App\Lib\Caches\Base;


use App\Lib\Caches\CacheBase;

class File extends CacheBase
{
    /**
     * 文件类型
     * @var string
     */
    public $cacheType = 'file';

}