<?php
/**
 * @filename Video.php
 * @desc this is file description
 * @date 2020/6/28 17:00
 * @author: wsr
 */

namespace App\HttpController\Api;

use App\HttpController\Api\ApiBase;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Http\Message\Status;
use App\Model\VideoModel;
use EasySwoole\Validate\Validate;
use EasySwoole\Validate\Rule;

class Video extends ApiBase
{
    private $logType = 'video';
    protected function validateRule(): ?Validate
    {
        $validate = new Validate();
        $validate->addColumn('name','视频名称')->required('不能为空')->lengthMin(4,'长度错误');
        $validate->addColumn('image','图片地址')->required('不能为空');
        $validate->addColumn('url')->required('不能为空')->lengthMin(1,'不能为空');;
        return $validate;
    }

    public function add()
    {
        $params = $this->request()->getRequestParam();
        $this->writeLog(sprintf("%s|%s:%s",$this->logType, 'add-request', json_encode($params)));
        $validate = $this->validateRule();
        $ret = $validate->validate($params);
        if($ret == false){
            $msg = "{$validate->getError()->getField()}@{$validate->getError()->getFieldAlias()}:{$validate->getError()->getErrorRuleMsg()}";
            $this->writeJson(Status::CODE_BAD_REQUEST, $msg);
            $this->writeLog(sprintf("%s|%s:%s",$this->logType, 'add-validate-failed', $msg));
            return false;
        }

        $data = [
            "name" => $params["name"] ?? "",
            "image" => $params["image"] ?? "",
            "url" => $params["url"] ?? "",
            "content" => $params["content"] ?? "",
            "cat_id" => (int) $params["cat_id"] ?? 0,
            "create_time" => time(),
            "status" => VideoModel::STATUS_NO_AUDITED
        ];

        try {
            $vedioId = VideoModel::create()->data($data, false)->save();
        } catch (\Exception $e) {
            $this->writeLog(sprintf("%s|%s:%s",$this->logType, 'add-failed', $e->getMessage()));
            return $this->writeJson(STATUS::CODE_BAD_REQUEST, $e->getMessage());
        }

        if ($vedioId) {
            $this->writeJson(STATUS::CODE_OK, "SUCCESS", ['id' => $vedioId]);
        } else {
            $this->writeJson(30001,"FAILED");
        }
    }

}