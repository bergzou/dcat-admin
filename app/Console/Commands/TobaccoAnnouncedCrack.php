<?php

namespace App\Console\Commands;

use App\Libraries\Alibaba\AliOCR;
use App\Libraries\Baidu\BaiDuOCR;
use App\Libraries\Tesseract;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TobaccoAnnouncedCrack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TobaccoAnnouncedCrack';

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
        $tesseract = new  Tesseract();

        try {
            $announcedData = DB::table('tobacco_announced')->where('announced_crack', 1)->get()->toArray();

            if (!empty($announcedData)) {
                $directory = storage_path('/images');
                foreach ($announcedData as $announced) {
                    $announcedContent = json_decode($announced->announced_content, true);
                    if (!empty($announcedContent)) {
                        $filePathData = array_column($announcedContent, 'filePath');
                        foreach ($filePathData as $filePath) {

                            $filePath = '/api/applet' . $filePath;
                            $url = $this->downloadImage($directory, $filePath);
                            $command = "python ocr.py " . escapeshellarg($url);
                            $output = shell_exec($command . " 2>&1");
                            $resData = $tesseract->processOcrData($output);
                            if (!empty($resData)) {
                                $insertData = [];
                                foreach ($resData as $company) {
                                    if (empty($company)) continue;
                                    $insertData[] = [
                                        'company_name' => $company,
                                        'company_area' => $announced->announced_area,
                                        'company_addr' => "no",
                                        'company_phone' => "no",
                                        'company_person' => "no",
                                        'company_type' => $announced->announced_status,
                                    ];
                                }
                                DB::table('tobacco_company_new')->insert($insertData);

                            }

                        }
                    }
                    var_dump($announced->announced_title);
                    $updateData = [
                        'updated_at' => date("Y-m-d H:i:s"),
                        'announced_crack' => 2

                    ];
                    DB::table('tobacco_announced')->where('id', $announced->id)->update($updateData);

                }
            }
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            die;
        }

    }


    public function downloadImage($directory, $imageUrl)
    {

        if (empty($imageUrl)) return response()->json(['error' => '缺少图片 URL'], 400);
        // 下载图片
        try {
            // 创建目录
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0777, true, true);
            }
            // https://hlbj.sztobacco.cn/api/applet/images/upload/publicity/2025-03/73870bc8-5b97-49ff-b467-c821004e3c4a.png
            $client = new Client(['base_uri' => 'https://hlbj.sztobacco.cn', 'verify' => false]);
            $response = $client->request('GET', $imageUrl);
            $imageContent = $response->getBody()->getContents();
            // 生成文件名
            $filename = uniqid() . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
            $filePath = $directory . '/' . $filename;
            // 保存图片
            File::put($filePath, $imageContent);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $filePath;
    }
}
