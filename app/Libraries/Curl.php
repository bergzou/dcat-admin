<?php
/**
 * @Notes: CURL 工具类
 * @Date: 2024/3/18
 * @Time: 11:26
 * @Interface CurlTool
 * @return
 */


namespace App\Libraries;


use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Lang;


class Curl
{
    /**
     * Notes: 发送CURL请求
     * Date: 2024/3/18 11:27
     * @param $url //请求的URL地址
     * @param $method //请求的方法，支持GET、POST、PUT、DELETE等
     * @param $data //请求的数据，关联数组格式，例如：['name' => '张三', 'age' => 20]
     * @param $headers //请求的头信息，关联数组格式，例如：['Content-Type' => 'application/json']
     * @param $timeout //请求的超时时间，单位为秒，默认为30秒
     * @param $maxRetries //请求的最大重试次数，默认为0，表示不重试
     * @param $retryDelay //请求重试的延迟时间，单位为毫秒，默认为0，表示不延迟
     * @param $https  //是否为 https 默认是
     * @return array|mixed 返回请求的结果，请求失败返回false
     * @throws BusinessException
     */
    public static function sendRequest($url, $method = 'GET', $data = [], $headers = array("Content-Type: application/json;charset=UTF-8"), $timeout = 30, $maxRetries = 0, $retryDelay = 0, $https = true )
    {
        // 初始化CURL
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
        }

        // 设置请求的方法
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        if(!empty($data)){
            // 设置cURL选项
            switch (strtoupper($method)) {
                case 'GET':
                    $url .= '?' . http_build_query($data);
                    break;
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    break;
                case 'PUT':
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    break;
            }
        }
        // 设置请求的URL地址
        curl_setopt($ch, CURLOPT_URL, $url);

        // 设置请求的头信息
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // 设置请求的超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        // 设置请求的重试次数和延迟时间
        if ($maxRetries > 0) {
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $retryDelay);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout * 1000);
        }

        // 执行请求
        $result = false;
        $retryCount = 0;
        do {
            $result = curl_exec($ch);
            $retryCount++;
        } while ($maxRetries > 0 && $result === false && $retryCount <= $maxRetries);

        var_dump($result);;die;
        // 记录日志






        return $result;
    }
    /**
     * 使用 CURL 将文件上传到远程服务器
     * @param string $localFile 本地文件路径
     * @param string $remoteUrl 远程服务器地址
     * @param string $fileName 上传文件名
     * @return mixed CURL 请求的响应结果
     */
    public function curlUpload($localFile, $fileName = '')
    {
        $remoteUrl = env('FILE_URL').'/inner/file/commonUpload';
        // 创建 CURLFile 对象
        $file_path = $localFile;
        $file_name = $fileName ?: basename($file_path);
        $mime_type = mime_content_type($file_path);
        $file = curl_file_create($file_path, $mime_type, $file_name);

        // 创建 CURL 请求
        $ch = curl_init($remoteUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $file]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 执行 CURL 请求
        $response = curl_exec($ch);
        // 关闭 CURL 请求
        curl_close($ch);
        // 记录日志
        Logger::curlUpload($remoteUrl,$file_name,curl_error($ch));
        // 返回响应结果
        return $response;
    }

    /**
     * 使用 CURL 将文件上传到远程服务器
     * @param string $url 远程服务器地址
     * @param string $filePath 本地文件路径
     * @param string $fileName 上传文件名
     * @return mixed CURL 请求的响应结果
     */
    public static function sendFile(string $url, string $filePath, string $fileName = '')
    {
        // 创建 CURLFile 对象
        if(empty($fileName)){
            $fileName = basename($filePath);
        }

        $mimeType = mime_content_type($filePath);
        $file      = curl_file_create($filePath, $mimeType, $fileName);

        // 创建 CURL 请求
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $file]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 执行 CURL 请求
        $response = curl_exec($ch);

        // 记录日志
        Logger::curlUpload($url, $fileName, curl_error($ch));

        # 获取状态码赋值
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //判断状态码如果不是200则报错
        if ($httpCode != 200) {
            throw new BusinessException(Lang::get('web.50201') . $httpCode);
        }

        // 关闭 CURL 请求
        curl_close($ch);

        // 返回响应结果
        return $response;
    }
}
