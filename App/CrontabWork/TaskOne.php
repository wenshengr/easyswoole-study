<?php
namespace App\CrontabWork;
/**
 * @filename TaskOne.php
 * @desc this is file description
 * @date 2020/7/1 16:39
 * @author: wsr
 */

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Task\TaskManager;

class TaskOne extends AbstractCronTask
{

    public static function getRule(): string
    {
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        return  'taskOne';
    }

    function run(int $taskId, int $workerIndex)
    {
        var_dump('c');
        TaskManager::getInstance()->async(function (){
            var_dump('r');
        });
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // TODO: Implement onException() method.
        echo $throwable->getMessage();
    }
}