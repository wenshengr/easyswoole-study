<?php
/**
 * @filename VideoModel.php
 * @desc this is file description
 * @date 2020/6/30 18:04
 * @author: wsr
 */

namespace App\Model;

use App\Model\BaseModel;

/**
 * Class VideoModel
 * Create With Automatic Generator
 * @property $id int | 视频id
 * @property $name string | 视频名称
 * @property $cat_id int | 分类ID
 * @property $image string | 图片
 * @property $url string | 视频地址
 * @property $type int | 类型
 * @property $content string | 内容
 * @property $uploader string | 上传信息
 * @property $status int | 状态
 * @property $create_time int | 创建时间
 * @property $update_time int | 更新时间
 */
class VideoModel extends BaseModel
{
    /**
     * @var string
     */
    protected $tableName = 'video';

    /**
     * @var string
     */
    protected $primaryKey = 'Id';

    /**
     * 未审核
     */
    const STATUS_NO_AUDITED = 0;
    /**
     * 已审核
     */
    const STATUS_AUDITED = 1;
    /**
     * 已删除
     */
    const STATUS_DELETED = 2;

    public function getVideoList($where = [], $page = 1, $pageSize = 10)
    {
        if (!empty($where['cat_id'])) {
            $this->where('cat_id', $where['cat_id']);
        }
        $this->where('status', self::STATUS_AUDITED);
        $list = $this->limit($pageSize * ($page - 1), $pageSize)
            ->order($this->primaryKey, 'DESC')
            ->withTotalCount()
            ->all($where);
        $total = $this->lastQueryResult()->getTotalCount();

        return [
            'page_size' => $pageSize,
            'total_num' => $total,
            'total_page' => ceil($total / $pageSize),
            'list' => $list
        ];
    }

    /**
     * @param array $where
     * @param int $pageSize
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getVideoCacheData($where = [], $pageSize = 1000)
    {
        if (!empty($where['cat_id'])) {
            $this->where('cat_id', $where['cat_id']);
        }
        $this->where('status', self::STATUS_AUDITED);
        return $this->limit(0, $pageSize)
            ->order($this->primaryKey, 'DESC')
            ->withTotalCount()
            ->all($where);
    }
}