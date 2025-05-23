<?php

namespace App\Console\Commands;

use App\Libraries\Curl;
use Dotenv\Parser\Value;
use Illuminate\Console\Command;
use App\Models\Tobacco as TobaccoModel;
use Illuminate\Support\Facades\DB;

class TianYanCha1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TianYanCha1';

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

        $tobaccoCompany = DB::table('tobacco_company_tianyancha')->where('company_crack',1)->limit(10000)->orderBy('id','desc')->get()->toArray();

        if (!empty($tobaccoCompany)){

            foreach ($tobaccoCompany as $value){

                $mtRandNumber = mt_rand(1, 5);

                $stopTime = 2 + $mtRandNumber;
                sleep($stopTime);

                var_dump(date("Y-m-d H:i:s").":".$value->company_name);

                $res = $this->search($value->company_name);


                if (!empty($res)){
                    foreach ($res as $key => $companyData ){

                        $companyPhone = $companyData['phoneNum'] ?? "wu";
                        $companyPhoneArr = $companyData['phoneList'] ?? [];

                        $companyName = $companyData['name'] ?? '';
                        $companyName = strip_tags($companyName);
                        $company = DB::table('tobacco_company')->where('company_name',$companyName)->first();
                        if (empty($company)){
                            $insertData = [
                                'company_name' => $companyName,
                                'company_area' => $companyData['districtName'] ?? '',
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


                }
                DB::table('tobacco_company_new')->where('id',$value->id)->update(['company_crack' => 2]);
            }
        }

    }


    public function searchNew($searchKey){
        $token = "ddb78af8-c78c-4c19-9e05-07ecfb97e4f0";
        $url="http://open.api.tianyancha.com/services/open/search/2.0?word=".urlencode($searchKey)."&pageSize=".urlencode(3)."&pageNum=".urlencode(1);

        $curl = curl_init();
        $header = ["Authorization: $token"];

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $data = curl_exec($curl);
        curl_close($curl);

        print_r($data);
    }


    public function search($searchKey){


        $url = 'https://www.tianyancha.com/nsearch?key=' . urlencode($searchKey);


        $cookieStr = 'HWWAFSESTIME=1745551912585; HWWAFSESID=d3ce917bee00fd55d0c; csrfToken=Zx434odEZrqGti7_R6yeYg7w; CUID=d2482a6d7dce2857e664562a099191d7; jsid=SEO-BAIDU-ALL-SY-000001; TYCID=d6ab8fa0218511f0be8e0fc96c251868; sajssdk_2015_cross_new_user=1; bdHomeCount=0; Hm_lvt_e92c8d65d92d534b0fc290df538b4758=1745551917; HMACCOUNT=54A9DB1A3718F846; bannerFlag=true; tyc-user-info=%7B%22state%22%3A%220%22%2C%22vipManager%22%3A%220%22%2C%22mobile%22%3A%2215914001478%22%2C%22userId%22%3A%22347513683%22%7D; tyc-user-info-save-time=1745552236294; auth_token=eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxNTkxNDAwMTQ3OCIsImlhdCI6MTc0NTU1MjIzNiwiZXhwIjoxNzQ4MTQ0MjM2fQ.uZQRcIWpd6k6II3nKM7PU8WXg26d6PwRzskzMDW1KRr1NgnaHpVqgWjUqsUaaQ0Axn_BS5JVMpGfs68HJZtrHg; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%22347513683%22%2C%22first_id%22%3A%221966b007e5e92f-0687c2c1b401f78-4c657b58-2073600-1966b007e5f11f4%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E7%9B%B4%E6%8E%A5%E6%B5%81%E9%87%8F%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC_%E7%9B%B4%E6%8E%A5%E6%89%93%E5%BC%80%22%2C%22%24latest_referrer%22%3A%22%22%7D%2C%22identities%22%3A%22eyIkaWRlbnRpdHlfY29va2llX2lkIjoiMTk2NmIwMDdlNWU5MmYtMDY4N2MyYzFiNDAxZjc4LTRjNjU3YjU4LTIwNzM2MDAtMTk2NmIwMDdlNWYxMWY0IiwiJGlkZW50aXR5X2xvZ2luX2lkIjoiMzQ3NTEzNjgzIn0%3D%22%2C%22history_login_id%22%3A%7B%22name%22%3A%22%24identity_login_id%22%2C%22value%22%3A%22347513683%22%7D%2C%22%24device_id%22%3A%221966b007e5e92f-0687c2c1b401f78-4c657b58-2073600-1966b007e5f11f4%22%7D; searchSessionId=1745552331.55845826; Hm_lpvt_e92c8d65d92d534b0fc290df538b4758=1745552393';
        $headers = [
            'Host: www.tianyancha.com',
            'Connection: keep-alive',
            'sec-ch-ua: "Microsoft Edge";v="135", "Not-A.Brand";v="8", "Chromium";v="135',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-User: ?1',
            'Sec-Fetch-Dest: document',
            'sec-ch-ua: "Chromium";v="109", "Not_A Brand";v="99"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'Referer: '.$url,
            'Accept-Encoding: gzip, deflate, br, zstd',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Cookie: '.$cookieStr
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略SSL验证（生产环境不推荐）
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); // 处理gzip压缩


        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        curl_close($ch);

        return $this->getScripts($response);


    }

    public function getScripts($html){
        if ($html === false) {
            return [];
        }
        // 2. 初始化DOM解析器

        $dom =  new \DOMDocument();
        libxml_use_internal_errors(true); // 忽略HTML5不规范标签的警告
        $dom->loadHTML($html);
        libxml_clear_errors();

        // 3. 查找目标脚本标签
        $scripts = $dom->getElementsByTagName('script');
        $nextData = null;

        foreach ($scripts as $script) {
            if ($script->getAttribute('id') === '__NEXT_DATA__') {
                $nextData = $script->nodeValue;
                break;
            }
        }


        if (!$nextData){
            var_dump('无法就取到数据');
            die;
        }


        $data = json_decode($nextData,true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return  [];
        }
        return $data['props']['pageProps']['listRes']['data']['companyList'] ?? [];
    }









}
