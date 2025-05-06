<?php

namespace App\Libraries;


use Intervention\Image\Facades\Image;
use thiagoalessio\TesseractOCR\TesseractOCR;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;

class Tesseract
{

    public function processOcrData($jsonData) {

        $data = json_decode($jsonData, true);
        $companies = [];
        $currentRow = [];
        $prevY = null;

        // 定义企业名称列的X坐标范围（根据实际数据调整）
        $companyXMin = 600;
        $companyXMax = 650;

        foreach ($data as $item) {
            $x = $item['position'][0][0];
            $y = $item['position'][0][1]; // 左上角Y坐标


            // 只处理企业名称列的条目
            if ($x >= $companyXMin && $x <= $companyXMax) {

                if ($item['text'] == '企业名称') continue;
                $currentRow[] = $item['text'];

            }
        }


        $companies = explode('深圳', implode('', $currentRow));

        $companies = array_map(function($value) {
            if (!empty($value))  return '深圳'.$value;
        }, $companies);

        return $companies;
    }

}





