<?php
/**
 * @filename ReflectClass.php
 * @desc this is file description
 * @date 2020/6/30 16:45
 * @author: wsr
 */

namespace App\Lib;

/**
 * 反射机制处理类
 * Class ReflectClass
 * @package App\Lib
 */
class ClassReflect
{
    /**
     * @return string[]
     */
    public function uploadClassStat()
    {
        return [
            'image' => '\App\Lib\Upload\Image',
            'video' => '\App\Lib\Upload\Video',
        ];
    }

    /**
     * @return string[]
     */
    public function cacheClassStat()
    {
        return [
            'CacheFile' => '\App\Lib\Cache\Base\File',
        ];
    }

    /**
     * @param $type
     * @param $supportedClass
     * @param array $params
     * @param bool $needInstance
     * @return bool|mixed|object
     * @throws \ReflectionException
     */
    public function initClass($type, $supportedClass, $params = [], $needInstance = true)
    {
        if (!array_key_exists($type, $supportedClass)) {
            return FALSE;
        }
        $className = $supportedClass[$type];

        return $needInstance ? (new \ReflectionClass($className))->newInstanceArgs($params) : $className;
    }
}