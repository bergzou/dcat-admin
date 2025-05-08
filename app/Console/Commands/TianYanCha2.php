<?php

namespace App\Console\Commands;

use App\Libraries\Curl;
use Dotenv\Parser\Value;
use Illuminate\Console\Command;
use App\Models\Tobacco as TobaccoModel;
use Illuminate\Support\Facades\DB;

class TianYanCha2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TianYanCha2';

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

        $tobaccoCompany = DB::table('tobacco_company_new')->where('company_crack',1)->limit(10000)->get()->toArray();

        if (!empty($tobaccoCompany)){

            foreach ($tobaccoCompany as $value){

                $mtRandNumber = mt_rand(1, 5);

                $stopTime = 3 + $mtRandNumber;
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

        $cookieStr = 'CUID=1371a429afc524aa27fa3005117d97c9; jsid=SEO-BAIDU-ALL-SY-000001; TYCID=b5ba65c02ae311f0924b5da3e5efc158; sensorsdata2015jssdkcross=%7B%22distinct_id%22%3A%22345422520%22%2C%22first_id%22%3A%22196a8632f39ef0-00f301c17e118ef-f525727-2073600-196a8632f3acfc%22%2C%22props%22%3A%7B%22%24latest_traffic_source_type%22%3A%22%E8%87%AA%E7%84%B6%E6%90%9C%E7%B4%A2%E6%B5%81%E9%87%8F%22%2C%22%24latest_search_keyword%22%3A%22%E6%9C%AA%E5%8F%96%E5%88%B0%E5%80%BC%22%2C%22%24latest_referrer%22%3A%22https%3A%2F%2Fwww.baidu.com%2Flink%22%7D%2C%22identities%22%3A%22eyIkaWRlbnRpdHlfY29va2llX2lkIjoiMTk2YTg2MzJmMzllZjAtMDBmMzAxYzE3ZTExOGVmLWY1MjU3MjctMjA3MzYwMC0xOTZhODYzMmYzYWNmYyIsIiRpZGVudGl0eV9sb2dpbl9pZCI6IjM0NTQyMjUyMCJ9%22%2C%22history_login_id%22%3A%7B%22name%22%3A%22%24identity_login_id%22%2C%22value%22%3A%22345422520%22%7D%2C%22%24device_id%22%3A%22196a8632f39ef0-00f301c17e118ef-f525727-2073600-196a8632f3acfc%22%7D; sajssdk_2015_cross_new_user=1; bdHomeCount=4; bannerFlag=true; Hm_lvt_e92c8d65d92d534b0fc290df538b4758=1746581795,1746586389; tyc-user-info-save-time=1746586526125; searchSessionId=1746586591.04966652; HWWAFSESID=e0e338edbbc3ef0dd9; HWWAFSESTIME=1746586387783; csrfToken=1RBTsZihXAMOK8jaVxH2_wJY; Hm_lpvt_e92c8d65d92d534b0fc290df538b4758=1746586593; HMACCOUNT=7B768161E1572EB9; tyc-user-info=%7B%22state%22%3A%220%22%2C%22vipManager%22%3A%220%22%2C%22mobile%22%3A%2217722990502%22%2C%22userId%22%3A%22345422520%22%7D; auth_token=eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxNzcyMjk5MDUwMiIsImlhdCI6MTc0NjU4NjUyNSwiZXhwIjoxNzQ5MTc4NTI1fQ.fdKf9iNnxpuOeJIFrXGT8euPry8Y_osFqVUGsaGxVByNRvDEyvXrnpB0MDb7iaUdFHOVFiSZQf82RIzE7Hptlg';

        $headers = [
            'Host: www.tianyancha.com',
            'Connection: keep-alive',
            'sec-ch-ua: "Microsoft Edge";v="135", "Not-A.Brand";v="8", "Chromium";v="135',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:138.0) Gecko/20100101 Firefox/138.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-User: ?1',
            'Sec-Fetch-Dest: document',
            'sec-ch-ua: "Chromium";v="109", "Not_A Brand";v="99"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'Referer: '.$url,
            'Accept-Encoding: gzip, deflate, br, zstd',
            'Accept-Language: zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2',
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
