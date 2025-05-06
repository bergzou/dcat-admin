<?php
/**
 * @Notes: 日志工具类
 * @Date: 2024/3/19
 * @Time: 20:34
 * @Interface Logger
 * @return
 */


namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\JsonResponse;

class Logger
{
    /**
     * Notes: 记录http请求日志
     * Date: 2024/3/20 13:56
     * @param Request $request
     * @param JsonResponse $response
     */
    public static function httpRequest(Request $request , JsonResponse $response)
    {
        if (!empty($response)){
            $path = $request->path();
            // 记录请求信息
            $center = [
                'url' => $request->url(),
                'req' => $request->all(),
                'res' => $response->getData(),
            ];
            Log::channel('internal_request')->info('请求时间:'.date("Y:m:d H:i:s").'  请求路由:'.$path ,$center);

        }


    }

    /**
     * Notes: 队列日志
     * Date: 2024/3/25 13:58
     */
    public static function queue()
    {

    }
    /**
     * Notes: 阿波罗配置
     * Date: 2024/3/25 13:58
     */
    public static function apollo()
    {

    }

    /**
     * Notes: BusinessException
     * Date: 2024/3/25 13:58
     * @param $message
     * @return bool
     */
    public static function business($message)
    {
        if (!empty($message)){
            Log::channel('business')->info(
                '请求时间:'.date("Y:m:d H:i:s").'  请求路由:'.URL::current(),
                [
                'message' => $message,
                ]
            );
        }
        return true;
    }

    /**
     * Notes: ThrowableException
     * Date: 2024/3/25 13:56
     * @param $file
     * @param $line
     * @param $message
     * @return bool
     */
    public static function throwable($file, $line, $message)
    {
        if (!empty($message)){
            Log::channel('throwable')->info(
                '请求时间:'.date("Y:m:d H:i:s").'  请求路由:'.URL::current(),
                [
                    'file' => $file,
                    'line' => $line,
                    'message' => $message,
                ]
            );
        }
        return true;
    }
    /**
     * Notes: 上传文件
     * Date: 2024/3/25 13:56
     */
    public static function curlUpload($remoteUrl, $file_name, $message)
    {
        if (!empty($message)){

            Log::channel('curl_upload')->info(
                '请求时间:'.date("Y:m:d H:i:s").'  请求路由:'.URL::current(),
                [
                    'url' => $remoteUrl,
                    'file_name' => $file_name,
                    'message' => $message,
                ]
            );

        }
        return true;
    }

    /**
     * Notes: 调用内部系统日志
     * Date: 2024/3/20 14:46
     */
    public static function clientRequest($url, $req, $res , $message = '')
    {
        $center = [
            'url' => $url,
            'req' => $req,
            'res' => $res,
            'message' => $message,
        ];
        Log::channel('client_request')->info('请求时间:'.date("Y:m:d H:i:s").'  请求路由:'.URL::current() ,$center);
        return true;
    }

    public static function internalRequest($url, $req, $res , $message = '')
    {
        $center = [
            'url' => $url,
            'req' => $req,
            'res' => $res,
            'message' => $message,
        ];
        Log::channel('internal_request')->info('请求时间:'.date("Y:m:d H:i:s").'  请求路由:'.URL::current() ,$center);
        return true;
    }


    //业务日志
    public static function businessLog($message,array $args)
    {
        if (!empty($args)){

            foreach ($args as &$argV) {
                if (is_array($argV)) {
                    try {
                        $argV = json_encode($argV, JSON_UNESCAPED_UNICODE);
                        if ($argV === false) {
                            $argV = 'JSON encoding failed';
                        }
                    } catch (\Exception $e) {
                        $argV = '[无法序列化的数组]';
                    }
                }
            }

            $messageArr = array_filter(explode('%s',$message));
            if (count($messageArr) != count($args)){
                $message = '';
                foreach ($messageArr as $key => $value){
                    if ($key < count($args)){
                        $message .= $value .' %s';
                    }else{
                        $message .= $value;
                    }
                }
            }
//            $message = sprintf($message,...$args);
            $message = vsprintf($message,$args);
        }

        if (!empty($message)){
            Log::channel('business')->info(
                '请求时间:'.date("Y:m:d H:i:s"),
                [
                    $message
                ]
            );
        }

        return true;
    }



}
