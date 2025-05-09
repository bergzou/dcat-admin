<?php

namespace App\Console\Commands;

use App\Libraries\Curl;
use Dotenv\Parser\Value;
use Illuminate\Console\Command;
use App\Models\Tobacco as TobaccoModel;
use Illuminate\Support\Facades\DB;

class TianYanCha3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TianYanCha3';

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

        ini_set ("memory_limit","-1");

        $tobaccoCompany = DB::table('tobacco_company_tianyancha')->where('company_crack',1)->orderBy('id','desc')->get()->toArray();

        if (!empty($tobaccoCompany)){

            foreach ($tobaccoCompany as $value){

                $mtRandNumber = mt_rand(1, 10);
                $stopTime = 5 + $mtRandNumber;

                sleep(1);

                var_dump(date("Y-m-d H:i:s").":".$value->company_name);

                $res = $this->search($value->company_name);


                if ($res['http_code'] == 200 && !empty($res['response'])){

                    $companyList = $res['response']['data']['companyList'] ?? [];


                    if (empty($companyList)){
                        var_dump(json_encode($res,true));
                        continue;
                    }
                    foreach ($companyList as $key => $companyData){

                        $companyPhone = $companyData['phoneNum'] ?? "wu";
                        $companyPhoneArr = $companyData['phoneList'] ?? [];

                        $companyName = $companyData['name'] ?? '';
                        $companyName = strip_tags($companyName);
                        $company = DB::table('tobacco_company')->where('company_name',$companyName)->first();
                        if (empty($company)){
                            $insertData = [
                                'company_name' => $companyName,
                                'company_area' => $companyData['district'] ?? '',
                                'company_person' => $companyData['legalPersonName']?? '',
                                'company_phone' => $companyPhone,
                                'company_phone_json' => json_encode($companyPhoneArr),
                                'company_level' => $key,
                                'created_at' => date("Y-m-d H:i:s"),
                                'updated_at' => date("Y-m-d H:i:s"),
                            ];
                            DB::table('tobacco_company')->insert($insertData);
                        }
                    }

                }else{
                    var_dump($res);die;
                }

                DB::table('tobacco_company_new')->where('id',$value->id)->update(['company_crack' => 2]);
            }
        }

    }





    public function search($searchKey): array
    {



        $postData = [
            'sortType' => 0,
            'pageSize' => 20,
            'pageNum' => 1,
            'word' => $searchKey,
            'allowModifyQuery' => 1
        ];
        $url = 'https://capi.tianyancha.com/cloud-tempest/app/searchCompany';

        $authorization = '0###oo34J0UzeHZOfEVuk6hUqPwxfkBY###1746683119885###d8d4f403759d18b03bb087a5f5de940b';
        $token = 'eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxODMxODMzNjYyNSIsImlhdCI6MTc0NjY4MzU3NiwiZXhwIjoxNzQ5Mjc1NTc2fQ.A3o4nYULOjjLy4-9MwjhVKc6KnNUciKIeB5HbHLsgOHm9yorYNM8u5h-GSHAcp5PC8dzBALiKUChlzsgM8Etkg';
        // 构造请求头
        $headers = [
            'Host: capi.tianyancha.com',
            'Connection: keep-alive',
            'Content-Type: application/json',
            'xweb_xhr: 1',
            'Authorization: ' . $authorization,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 MicroMessenger/7.0.20.1781(0x6700143B) NetType/WIFI MiniProgramEnv/Windows WindowsWechat/WMPF WindowsWechat(0x63090c33)XWEB/11581',
            'version: TYC-XCX-WX',
            'Accept: */*',
            'Sec-Fetch-Site: cross-site',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Referer: https://servicewechat.com/wx9f2867fc22873452/115/page-frame.html',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9'
        ];

        // 初始化CURL
        $ch = curl_init();

        // 设置CURL选项
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,   // 返回响应结果
            CURLOPT_POST           => true,   // POST方法
            CURLOPT_POSTFIELDS     => json_encode($postData, JSON_UNESCAPED_UNICODE),  // JSON数据
            CURLOPT_HTTPHEADER     => $headers,       // 请求头
            CURLOPT_SSL_VERIFYPEER => false,   // 验证SSL证书
            CURLOPT_SSL_VERIFYHOST => 2,      // 严格校验主机名
            CURLOPT_ENCODING       => 'gzip', // 自动解压响应
        ]);

        // 执行请求
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 关闭连接
        curl_close($ch);

        // 错误处理
        if ($error) {
            return [
                'error' => $error,
                'http_code' => $httpCode
            ];
        }

        return [
            'http_code' => $httpCode,
            'response'  => json_decode($response, true)  // 自动解析JSON响应
        ];


    }









}
