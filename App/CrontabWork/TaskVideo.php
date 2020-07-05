<?php
namespace App\CrontabWork;
/**
 * @filename TaskVideo.php
 * @desc this is file description
 * @date 2020/7/1 16:39
 * @author: wsr
 */

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Task\TaskManager;
use App\Lib\Caches\Video;

class TaskVideo extends AbstractCronTask
{

    public static function getRule(): string
    {
        // todo 每分钟执行一次
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        return  'taskVideo';
    }

    function run(int $taskId, int $workerIndex)
    {
        $videoCache = new Video();
        $videoCache->setIndexVideo();
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        echo $throwable->getMessage();
    }
}