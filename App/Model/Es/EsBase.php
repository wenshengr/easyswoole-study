<?php
/**
 * @filename EsVideo.php
 * @desc this is file description
 * @date 2020/7/3 10:36
 * @author: wsr
 */

namespace App\Model\Es;


use EasySwoole\Component\Di;

class EsBase
{
    public $esClient = null;
    public function __construct()
    {
        $this->esClient = Di::getInstance()->get('ES');
    }

    /**
     * @param $name
     * @param int $from
     * @param int $size
     * @param string $searchType
     * @return array
     */
    public function searchByName($name, $from = 0, $size = 10, $searchType = 'match')
    {
        $name = trim($name);
        if (!$name) {
            return [];
        }
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                'query' => [
                    $searchType => [
                        'name' => $name
                    ]
                ],
                'from' => $from,
                'size' => $size
            ]
        ];


        return $this->esClient->search($params);
    }

    /**
     * @param $id
     * @return array
     * @throws \Throwable
     */
    public function searchById($id)
    {
        $id = trim($id);
        if (!$id) {
            return [];
        }
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $id
        ];

        return $this->esClient->get($params);
    }
}