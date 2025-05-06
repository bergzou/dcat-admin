<?php
/*
 * 基础工具类
 *
 */
namespace App\Libraries\Baidu;


class BaiDuOCR
{
    const API_KEY = "VHDHcLgCFvvBv5ehwKq88143";
    const SECRET_KEY = "CRVWjZoQIyPr1omrSPaUx4DEAAPioBx0";

    public $accessToken = '';

    public function ocr($filePath)
    {
        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://aip.baidubce.com/rest/2.0/ocr/v1/table?access_token={$this->getAccessToken()}",
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CUSTOMREQUEST => 'POST',

            CURLOPT_POSTFIELDS => http_build_query(array(
                'url' => $filePath,
                'cell_contents' => 'false',
                'return_excel' => 'false'
            )),

            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ),

        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * 使用 AK，SK 生成鉴权签名（Access Token）
     * @return string 鉴权签名信息（Access Token）
     */
    public function getAccessToken()
    {
        $curl = curl_init();
        $postData = array(
            'grant_type' => 'client_credentials',
            'client_id' => self::API_KEY,
            'client_secret' => self::SECRET_KEY
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://aip.baidubce.com/oauth/2.0/token',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($postData)
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $rtn = json_decode($response);
        return $rtn->access_token;
    }

    public function extractAndTransformData($jsonData) {

        var_dump($jsonData);
        $data = json_decode($jsonData,true);
        // 提取body数据
        $body = $data['tables_result'][0]['body'] ?? [];
        if (empty($body)) return [];


        // 计算最大行和列
        $maxRow = 0;
        $maxCol = 0;
        foreach ($body as $cell) {
            $maxRow = max($maxRow, $cell['row_end']);
            $maxCol = max($maxCol, $cell['col_end']);
        }

        // 初始化二维数组
        $table = array_fill(0, $maxRow, array_fill(0, $maxCol, ''));

        // 填充数据
        foreach ($body as $cell) {
            for ($r = $cell['row_start']; $r < $cell['row_end']; $r++) {
                for ($c = $cell['col_start']; $c < $cell['col_end']; $c++) {
                    if (isset($table[$r][$c])) {
                        $table[$r][$c] = $cell['words'];
                    }
                }
            }
        }

        // 转换为对象数组（排除表头）
        $result = [];
        foreach (array_slice($table, 1) as $row) {
            $result[] = [
                'number'      => $row[0] ?? '',
                'serial_code' => $row[1] ?? '',
                'company'    => str_replace("\n", '', $row[2] ?? ''),
                'addr'       => str_replace("\n", '', $row[3] ?? ''),
                'user_name'  => $row[4] ?? ''
            ];
        }

        return $result;
    }



}





