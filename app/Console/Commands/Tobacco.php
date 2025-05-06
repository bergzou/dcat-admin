<?php

namespace App\Console\Commands;

use App\Libraries\Curl;
use Dotenv\Parser\Value;
use Illuminate\Console\Command;
use App\Models\Tobacco as TobaccoModel;
use Illuminate\Support\Facades\DB;

class Tobacco extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Tobacco';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tobaccoData = TobaccoModel::query()->where('id','>',0)
            ->where('tobacco_type',1)->get()->toArray();
        if (!empty($tobaccoData)){

            foreach ($tobaccoData as $value){

                $body = json_encode([
                    'keyword' => "",
                    'pageSize' => 200,
                    'pageNum' => 1,
                    'area' => $value['tobacco_area']
                ]);

                list($xAppid,$xTime,$xNonce,$xSign) = $this->generateXSign($value['tobacco_app_id'],$value['tobacco_app_secret']);
                $headers = [
                    'Host: hlbj.sztobacco.cn',
                    'Content-Type: application/json',
                    'X-Token: ' . $value['tobacco_token'],
                    'X-Sign: ' . $xSign,
                    'X-Appid: ' . $xAppid, // 固定AppId
                    'X-Time: ' . $xTime,
                    'X-Nonce: ' . $xNonce,
                    'xweb_xhr: 1',
                ];

                // 发送POST请求
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $value['tobacco_url'],
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false, // 生产环境建议启用SSL验证
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_ENCODING => 'gzip' // 处理压缩响应
                ]);

                $response = curl_exec($ch);

                $responseData = json_decode($response,true);

                if (!empty($responseData)){
                    $rows = $responseData['data']['rows'] ?? [];
                    foreach ($rows as $vv){

                        $announced = DB::table('tobacco_announced')->where('announced_id',$vv['id'])->first();
                        if (empty($announced)){
                            // 构建请求 URL
                            $url = "https://hlbj.sztobacco.cn/api/applet/home/getPublicityById?id=" . urlencode($vv['id']);
                            // 配置 cURL 选项
                            $ch = curl_init();
                            curl_setopt_array($ch, [
                                CURLOPT_URL => $url,
                                CURLOPT_HTTPHEADER => $headers,
                                CURLOPT_RETURNTRANSFER => true,      // 返回响应内容
                                CURLOPT_HEADER => false,             // 不包含响应头
                                CURLOPT_SSL_VERIFYPEER => false,      // 验证 SSL 证书
                                CURLOPT_SSL_VERIFYHOST => 2,         // 严格检查主机名
                                CURLOPT_ENCODING => 'gzip, deflate', // 处理压缩响应
                                CURLOPT_TIMEOUT => 15,               // 超时时间 15 秒
                            ]);

                            $announcedDetail = curl_exec($ch);
                            $announcedDetail = json_decode($announcedDetail,true);
                            if ($announcedDetail){

                                $tableName = $announcedDetail['data']['images'] ?? [];
                                $tableName = json_encode($tableName);
                                $insetData = [
                                    'announced_id' => $vv['id'],
                                    'announced_status' => $value['tobacco_type'],
                                    'announced_area' => $value['tobacco_area'],
                                    'announced_title' => $vv['title'],
                                    'announced_content' => $tableName,
                                    'announced_at' => $vv['createTime'],
                                ];
                                DB::table('tobacco_announced')->insert($insetData);
                            }
                        }
                    }
                }else{
                    var_dump($response);die;
                }
            }
        }
    }


    private  function generateXSign($appId,$secret): array
    {
        // 生成时间戳（秒级）
        $timestamp = time();

        // 生成16位随机字符串（与原代码 o.getRadomString() 对应）
        $nonce = bin2hex(random_bytes(8)); // 生成16位随机字符串

        // 拼接签名字符串
        $rawString = $appId . $secret . $timestamp . $nonce;

        // 计算MD5并取32位
        $xSign = substr(md5($rawString), 0, 32);

        return [
            $appId,
            $timestamp,
            $nonce,
            $xSign
        ];
    }

    /**
     * 生成随机Nonce（示例：AdFpbaOkPe）
     */
    private  function generateNonce(): string
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);
    }

}
