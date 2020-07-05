<?php
/**
 * @filename BaseModel.php
 * @desc this is file description
 * @date 2020/6/30 18:06
 * @author: wsr
 */

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class BaseModel extends AbstractModel
{
    public function __construct(array $data = [])
    {
        if (!$this->tableName) {
            throw new \Exception('table error!');
        }
    }

    /**
     * @param int $id
     * @return BaseModel|array|bool|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getDataById(int $id = 0)
    {
        $id = intval($id);
        if (!$id) {
            return [];
        }

        $result = $this->where('id', $id)->get();
        return $result ?? [];
    }
}