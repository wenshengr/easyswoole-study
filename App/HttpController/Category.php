<?php


namespace App\HttpController;
use EasySwoole\Http\AbstractInterface\Controller;

/**
 * 分类
 * Class Category
 * @package App\HttpController
 */
class Category extends Controller
{
    public function index()
    {
        $config = \Yaconf::get('category.cats');
        return $this->writeJson(200, 'OK', $config);
    }
}