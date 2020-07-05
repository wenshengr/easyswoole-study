<?php
/**
 * @filename EsClient.php
 * @desc this is file description
 * @date 2020/7/3 10:15
 * @author: wsr
 */

namespace App\Lib\Es;


use EasySwoole\Component\Singleton;
use Elasticsearch\ClientBuilder;

class EsClient
{
    use Singleton;

    public $esClient = null;
    private function __construct()
    {
        $config = \Yaconf::get('es');
        try {
            $this->esClient = ClientBuilder::create()->setHosts([$config['host'].':'.$config['port']])->build();
        } catch (\Exception $e) {
            // todo 报警邮件
            throw new \Exception('Es 连接失败');
        }

        if (empty($this->esClient)) {
            // todo 报警邮件
            throw new \Exception('Es 连接失败');
        }

    }

    public function __call($name, $arguments)
    {
        return $this->esClient->$name(...$arguments);
    }
}