<?php
/**
 * @filename ConsumerTest.php
 * @desc this is file description
 * @date 2020/6/29 20:09
 * @author: wsr
 */

namespace App\Lib\Process;

use EasySwoole\Component\Di;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Logger;

class ConsumerTest extends AbstractProcess
{
    private $isRun = false;
    public function run($arg)
    {
        // TODO: Implement run() method.
        /*
         * 举例，消费redis中的队列数据
         * 定时500ms检测有没有任务，有的话就while死循环执行
         */
        $this->addTick(500,function (){
            if(!$this->isRun){
                $this->isRun = true;
                while (true){
                    try{
                        $task = Di::getInstance()->get('REDIS')->lPop('consumer_test_list');
                        if($task){
                            // 发送邮件、推送消息
                            Logger::getInstance()->log($this->getProcessName().'----'.$task,Logger::LOG_LEVEL_INFO,'DEBUG');//记录info级别日志//例子后面2个参数默认值
                        }else{
                            break;
                        }
                    }catch (\Throwable $throwable){
                        break;
                    }
                }
                $this->isRun = false;
            }
            var_dump($this->getProcessName().' task run check');
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str, ...$args)
    {
        // TODO: Implement onReceive() method.
    }
}