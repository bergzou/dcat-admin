<?php
/*
 * 基础工具类
 *
 */
namespace App\Libraries\Alibaba;

class AliOCR
{

    public function ocr($filePath)
    {
        $url = "https://form.market.alicloudapi.com/api/predict/ocr_table_parse";
        $appcode = "6d57cb84080a46419d7d2e914dbd2113";
        $file = $filePath;
        //如果输入带有inputs, 设置为True，否则设为False
        $is_old_format = false;
        //如果没有configure字段，config设为空
        $config = array(
            "format" => "json",
            "finance" => false,
            "dir_assure" => false,
        );
        //$config = array()


        if ($fp = fopen($file, "rb", 0)) {
            $binary = fread($fp, filesize($file)); // 文件读取
            fclose($fp);
            $base64 = base64_encode($binary); // 转码
        }
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");
        $querys = "";
        if ($is_old_format == TRUE) {
            $request = array();
            $request["image"] = array(
                "dataType" => 50,
                "dataValue" => "$base64"
            );

            if (count($config) > 0) {
                $request["configure"] = array(
                    "dataType" => 50,
                    "dataValue" => json_encode($config)
                );
            }
            $body = json_encode(array("inputs" => array($request)));
        } else {
            $request = array(
                "image" => "$base64"
            );
            if (count($config) > 0) {
                $request["configure"] = json_encode($config);
            }
            $body = json_encode($request);
        }
        $method = "POST";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result_str = '';
        if ($httpCode == 200) {
            if ($is_old_format) {
                $output = json_decode($rbody, true);
                $result_str = $output["outputs"][0]["outputValue"]["dataValue"];
            } else {
                $result_str = $rbody;
            }
        }
        return $result_str;
    }


    private function extractStructuredData($jsonString)
    {
        $data = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON解析失败: " . json_last_error_msg());
        }

        $mapping = [
            0 => 'number',      // 序号
            1 => 'serial_code', // 轮候编码
            2 => 'company',     // 公司
            3 => 'addr',        // 经营地址
            4 => 'user_name'    // 经营者
        ];

        $result = [];
        foreach ($data['tables'] as $table) {
            foreach ($table as $rowCandidate) {
                $rawRow = [];
                $this->extractRowText($rowCandidate, $rawRow);

                // 关键过滤逻辑：必须包含全部5个有效字段
                if (count($rawRow) >= 5
                    && !empty($rawRow[1])  // 轮候编码必须存在
                    && !empty($rawRow[2])  // 公司名称必须存在
                ) {
                    $mappedRow = [];
                    foreach ($mapping as $index => $key) {
                        $mappedRow[$key] = $rawRow[$index] ?? null;
                    }
                    $result[] = $mappedRow;
                }
            }
        }
        return $result;
    }

    private function extractRowText($item, &$row)
    {
        if (is_array($item)) {
            if (isset($item['text'])) {
                $row[] = trim(implode(' ', $item['text']));
            } else {
                foreach ($item as $subItem) {
                    $this->extractRowText($subItem, $row);
                }
            }
        }
    }
}
