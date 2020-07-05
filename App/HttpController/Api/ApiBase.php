<?php
/**
 * @filename Index.php
 * @desc this is file description
 * @date 2020/6/28 17:00
 * @author: wsr
 */

namespace App\HttpController\Api;


use EasySwoole\EasySwoole\Logger;
use EasySwoole\Http\AbstractInterface\Controller;

class ApiBase extends Controller
{
    /**
     * 请求参数存储
     * @var array
     */
    public $params = [];
    public function onRequest(string $action): ?bool
    {
        $this->getParams();
        return true;
    }


    /**
     * 获取参数信息
     */
    public function getParams()
    {
        $params = $this->request()->getRequestParam();
        $params['page'] = !empty($params['page']) ? intval($params['page']) : 1;
        $params['pageSize'] = !empty($params['pageSize']) ? intval($params['pageSize']) : 5;
        $params['from'] = ($params['page'] - 1) * $params['pageSize'];
        $this->params = $params;
    }

    /**
     * 数组分页
     * @param int $count
     * @param $data
     * @param int $isSplice
     * @return array
     */
    public function getPagingDatas(int $count, $data, $isSplice = 1)
    {
        $totalPage = ceil($count / $this->params['pageSize']);
        $maxPageSize = \Yaconf::get('app.maxPageSize');
        if ($totalPage > $maxPageSize) {
            $totalPage = $maxPageSize;
        }
        $data = $data ?? [];
        if ($isSplice == 1) {
            $data = array_slice($data, $this->params['from'], $this->params['pageSize']);
        }
        return [
            'total_page' => $totalPage,
            'page_size' => $this->params['pageSize'],
            'total_num' => $count,
            'list' => $data
        ];
    }

//    public function onException(\Throwable $throwable): void
//    {
//        $this->writeJson(400, '无效的请求');
//    }


    /**
     * 记录日志
     * @param $params
     * @param string $logType
     * @param string $method
     * @param int $logLevel
     * @param string $category
     */
    public function writeLog($logMsg, $logLevel = Logger::LOG_LEVEL_INFO,$category = 'debug')
    {
        Logger::getInstance()->log($logMsg, $logLevel, $category);
    }

    /**
     * json数据格式输出
     *@statusCode
     */
    protected function writeJson($statusCode = 200, $message = null, $result = null){
        if(!$this->response()->isEndResponse()){
            $data = Array(
                "code"=>$statusCode,
                "message"=>$message,
                "result"=>$result
            );
            $this->response()->write(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type','application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        }else{
            trigger_error("response has end");
            return false;
        }
    }
}