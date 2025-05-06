<?php
/**
 * @Notes: 钉钉工具类
 * @Date: 2024/3/18
 * @Time: 11:38
 * @Interface DingDingTool
 * @return
 */


namespace App\Libraries;



class DingDing
{
    CONST URL = "https://oapi.dingtalk.com/robot/send?access_token=d83213def5bc033e16dad6bf8fee0d827ce156307ce09c01d5a8e7b4affe778e";  #群名：订单异常通知
    CONST ORDER_REPORT_URL = "https://oapi.dingtalk.com/robot/send?access_token=4e9d3eccd8c7c8cd1ea3eb123c2d4dde1e253c2446a6a1747e391a49c24bf6a5";	#群名：IT订单拉单沟通
    CONST DINGDING_TALK_LOCK = 'dingding_alarm_talk_lock';
    CONST SAME_LOCK_MIN = 60;//相同的告警信息几分钟告警一次

    /**
     * 推送钉钉
     * @param $msg
     * @return bool|string
     */
    public static function send($msg, $urlName = self::URL){

        $redis = Redisd::getInstance();
        if(!$redis->setNxLock(self::DINGDING_TALK_LOCK . '_' . md5($msg),self::SAME_LOCK_MIN * 60)){
            Common::log("上次告警未超过".self::SAME_LOCK_MIN."分钟，不进行重复告警",3);
            return false;
        }
        return false;
        $postData = [
            "msgtype" => "text",
            "text" => [
                'content' => "监控告警[{$env}]:" . $msg
            ]
        ];
        return CurlTool::sendRequest($urlName,'post',json_encode($postData));
    }

}
