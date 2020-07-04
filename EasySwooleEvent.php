<?php
namespace EasySwoole\EasySwoole;

use App\CrontabWork\TaskTwo;
use App\CrontabWork\TaskVideo;
use App\Lib\Es\EsClient;
#use App\Lib\Redis\Redis;
use App\Lib\Caches\Video;
use App\Lib\Db\Mysql;
use EasySwoole\Component\Di;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Bridge\Exception;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\FastCache\Cache;
use EasySwoole\FastCache\Exception\RuntimeError;
use EasySwoole\FastCache\CacheProcessConfig;
use EasySwoole\FastCache\SyncData;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Utility\File;
use App\Lib\Process\ConsumerTest;
use EasySwoole\Component\Process\Manager;
use App\Lib\Db\Redis;


class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');

        // 载入Config文件夹中的配置文件
        self::loadConfigFile();

        // 初始化 - 注册MySQL数据库
        Mysql::getInstance()->initDatabase();

        // 初始化 - 注册redis
        Redis::getInstance()->initRedis();
    }

    public static function mainServerCreate(EventRegister $register)
    {
        Di::getInstance()->set('ES', EsClient::getInstance());

        // 如何实现队列消费/自定义进程
        //self::testProcess();

        //毫秒任务计划
        //self::cronTaskWork($register);
    }

    /**
     * 毫秒任务计划
     * @param $register
     */
    private static function cronTaskWork($register)
    {
        // 开始一个定时任务计划 - 分钟级别
        //Crontab::getInstance()->addTask(TaskOne::class);
        //Crontab::getInstance()->addTask(TaskTwo::class);
        //Crontab::getInstance()->addTask(TaskVideo::class);


        // 开始一个定时任务计划 - 毫秒级别
        self::setFastCache();

        /* 毫秒定时器 */
        $videoCache = new Video();
//        Timer::getInstance()->loop(1 * 1000 * 60, function () use ($videoCache) {
//            $videoCache->setIndexVideo();
//        });

        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) use ($videoCache) {
            if ($workerId == 0) {
                Timer::getInstance()->loop(5 * 1000, function () use ($videoCache) {
                    $videoCache->setIndexVideo();
                });
            }
        });
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        self::setHeader($response);
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }

    /**
     * 加载配置文件
     */
    private static function loadConfigFile(): void
    {
        $scan = File::scanDirectory(EASYSWOOLE_ROOT . '/Config');
        if (is_array($scan) && isset($scan['files'])) {
            foreach ($scan['files'] as $file) {
                Config::getInstance()->loadFile($file);
            }
        }
    }

    /**
     * 设置header，解决跨域问题
     * @param Response $response
     */
    public static function setHeader(Response $response)
    {
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, token');
    }

    /**
     * 测试如何实现队列消费/自定义进程
     */
    private static function testProcess()
    {
        $allNum = 3;
        for ($i = 0 ;$i < $allNum;$i++){
            $processConfig= new \EasySwoole\Component\Process\Config();
            $processConfig->setProcessName('consumer_testp_'.$i);//设置进程名称
            Manager::getInstance()->addProcess(new ConsumerTest($processConfig));
        }
    }


    /**
     * 设置回调要在注册cache服务之前，注册服务之后不能更改回调事件。
     */
    protected static function setFastCache()
    {
        // 每隔5秒将数据存回文件
        try {
            Cache::getInstance()->setTickInterval(5 * 1000);//设置定时频率
            Cache::getInstance()->setOnTick(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
                $data = [
                    'data'  => $SyncData->getArray(),
                    'queue' => $SyncData->getQueueArray(),
                    'ttl'   => $SyncData->getTtlKeys(),
                    // queue支持
                    'jobIds'     => $SyncData->getJobIds(),
                    'readyJob'   => $SyncData->getReadyJob(),
                    'reserveJob' => $SyncData->getReserveJob(),
                    'delayJob'   => $SyncData->getDelayJob(),
                    'buryJob'    => $SyncData->getBuryJob(),
                ];
                $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
                File::createFile($path,serialize($data));
            });
        } catch (RuntimeError $e) {
            echo "[Warn] --> fast-cache注册onTick失败\n";
        }

        // 启动时将存回的文件重新写入
        try {
            Cache::getInstance()->setOnStart(function (CacheProcessConfig $cacheProcessConfig) {
                $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
                if(is_file($path)){
                    $data = unserialize(file_get_contents($path));
                    $syncData = new SyncData();
                    $syncData->setArray($data['data']);
                    $syncData->setQueueArray($data['queue']);
                    $syncData->setTtlKeys(($data['ttl']));
                    // queue支持
                    $syncData->setJobIds($data['jobIds']);
                    $syncData->setReadyJob($data['readyJob']);
                    $syncData->setReserveJob($data['reserveJob']);
                    $syncData->setDelayJob($data['delayJob']);
                    $syncData->setBuryJob($data['buryJob']);
                    return $syncData;
                }
            });
        } catch (RuntimeError $e) {
            echo "[Warn] --> fast-cache注册onStart失败\n";
        }

        // 在守护进程时,php easyswoole stop 时会调用,落地数据
        try {
            Cache::getInstance()->setOnShutdown(function (SyncData $SyncData, CacheProcessConfig $cacheProcessConfig) {
                $data = [
                    'data'  => $SyncData->getArray(),
                    'queue' => $SyncData->getQueueArray(),
                    'ttl'   => $SyncData->getTtlKeys(),
                    // queue支持
                    'jobIds'     => $SyncData->getJobIds(),
                    'readyJob'   => $SyncData->getReadyJob(),
                    'reserveJob' => $SyncData->getReserveJob(),
                    'delayJob'   => $SyncData->getDelayJob(),
                    'buryJob'    => $SyncData->getBuryJob(),
                ];
                $path = EASYSWOOLE_TEMP_DIR . '/FastCacheData/' . $cacheProcessConfig->getProcessName();
                File::createFile($path,serialize($data));
            });
        } catch (RuntimeError $e) {
            //echo "[Warn] --> fast-cache注册onShutdown失败\n";
            throw new Exception("[Warn] --> fast-cache注册onShutdown失败");
        }

        try {
            Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)
                ->attachToServer(ServerManager::getInstance()->getSwooleServer());
        } catch (\Exception $e) {
            //echo "[Warn] --> fast-cache注册失败\n";
            throw new Exception("[Warn] --> fast-cache注册失败");
        } catch (RuntimeError $e) {
            //echo "[Warn] --> fast-cache注册失败\n";
            throw new Exception("[Warn] --> fast-cache注册失败");
        }

    }
}