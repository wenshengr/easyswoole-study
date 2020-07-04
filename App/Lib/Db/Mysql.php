<?php
/**
 * @filename Mysql.php
 * @desc this is file description
 * @date 2020/6/29 14:29
 * @author: wsr
 */

namespace App\Lib\Db;

use EasySwoole\Component\Singleton;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;


class Mysql
{
    use Singleton;

    private $config = null;
    private function __construct()
    {
        $this->config =  \Yaconf::get('mysql');
    }

    /**
     * 初始化数据库
     */
    public function initDatabase()
    {
        $dbConfig = new \EasySwoole\ORM\Db\Config();
        $dbConfig->setUser($this->config['user']);
        $dbConfig->setPassword($this->config['password']);
        $dbConfig->setHost($this->config['host']);
        $dbConfig->setDatabase($this->config['database']);
        $dbConfig->setPort($this->config['port']);
        $dbConfig->setCharset($this->config['charset']);
        DbManager::getInstance()->addConnection(new Connection($dbConfig));
    }
}