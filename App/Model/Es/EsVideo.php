<?php
/**
 * @filename EsVideo.php
 * @desc this is file description
 * @date 2020/7/3 10:36
 * @author: wsr
 */

namespace App\Model\Es;

use \App\Model\Es\EsBase;

class EsVideo extends EsBase
{
    /**
     * 索引类型
     * @var string
     */
    public $index = 'video-test';
    public $type = 'test';
}