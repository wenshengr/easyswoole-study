<?php
/**
 * @filename Until.php
 * @desc this is file description
 * @date 2020/6/30 15:19
 * @author: wsr
 */

namespace App\Lib;


class Untils
{
    /**
     * 生成文件随机文件名
     * @param $str
     * @return false|string
     */
    public static function getFileFrontKey($str)
    {
        return substr(md5(self::getRandomStr() . $str. time(). rand(10000, 99999)), 16, 24);
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string
     */
    public static function  getRandomStr($length = 10)
    {
        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}