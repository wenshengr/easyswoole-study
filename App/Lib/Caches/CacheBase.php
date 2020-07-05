<?php
/**
 * @filename CacheBase.php
 * @desc this is file description
 * @date 2020/7/2 11:17
 * @author: wsr
 */

namespace App\Lib\Caches;

class CacheBase
{
    /**
     * 缓存文件类型
     * @var string
     */
    public $type = '';

    /**
     * 请求参数
     * @var array
     */
    public $reqParam;

    /**
     * 响应数据
     * @var array
     */
    public $resData;


    /**
     * CacheBase constructor.
     * @param $request
     * @param null $type
     * @param array $data
     */
    public function __construct($request, $type = null, $data = [])
    {
        $this->reqParam = $request;
        $this->resData = $data;
        if (!$type) {
            $this->type = \Yaconf::get('app.indexCacheType');
        } else {
            $this->type = $type;
        }
    }

    /**
     * 设置缓存
     * @return bool
     */
    public function setCache()
    {
        if ($this->type != $this->cacheType) {
            return false;
        }

        $module = $this->reqParam['module'];
        $index = $this->reqParam['index'];
        $extra = $this->reqParam['extra'];

        $fileName = $this->getCacheFileName($index, $extra);
        $funcName = 'set' . ucfirst($this->type) . 'Cache';
        try {
            $result = $this->$funcName($module, $fileName, $this->resData);
        } catch (\Exception $e) {
            // todo 报警处理
            $result = false;
        }
        return $result;
    }

    /**
     * 获取缓存
     * @return bool
     */
    public function getCache()
    {
        if (!$this->type) {
            return false;
        }

        $module = $this->reqParam['module'];
        $index = $this->reqParam['index'];
        $extra = $this->reqParam['extra'];

        $fileName = $this->getCacheFileName($index, $extra);
        $funcName = 'get' . ucfirst($this->type) . 'Cache';
        try {
            $result = $this->$funcName($module, $fileName, $this->resData);
        } catch (\Exception $e) {
            // todo 报警处理
            $result = false;
        }
        return $result;
    }

    /**
     * @param $index
     * @param $extra
     * @return string
     */
    public function getCacheFileName($index, $extra)
    {
        $str = is_array($extra) ? implode('_', $extra) : $extra;
        return $index . '_' . $str;
    }

}