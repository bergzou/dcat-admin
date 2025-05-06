<?php
/**
 *文件下载
 */
namespace App\Libraries;


class FileDownload
{
    const DOWNLOAD_CHUNK_SIZE = 1024 * 1024; // 每次读取 1MB

    /**
     * 文件下载
     *
     * @param array $filePath 文件路径
     * @param string $filename 文件名称
     */
    public static function download(string $filePath, string $fileName)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }

        $fileSize = filesize($filePath);
        $fileType = mime_content_type($filePath);

        header('Content-Type: ' . $fileType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Pragma: no-cache');
        header('Expires: 0');

        $fileStream = fopen($filePath, 'rb');
        while (!feof($fileStream)) {
            echo fread($fileStream, self::DOWNLOAD_CHUNK_SIZE);
            ob_flush();
            flush();
            sleep(1); // 控制下载速度
        }
        fclose($fileStream);
        exit();
    }

    /**
     * 文件url下载
     *
     * @param array $filePath 文件路径
     * @param string $chunkSize 分流下载多少
     */
    public static function downloadUrl($url, $chunkSize = 1024)
    {
        // 获取文件大小和名称
        $fileName = basename($url);
        $fileSize = self::getRemoteFileSize($url);

        // 设置响应头信息
        header('Content-Description: File Transfer'); // 文件传输描述
        header('Content-Type: application/octet-stream'); // 文件类型
        header('Content-Disposition: attachment; filename="'.$fileName.'"'); // 下载文件名
        header('Content-Transfer-Encoding: binary'); // 二进制编码
        header('Connection: Keep-Alive'); // 保持连接
        header('Expires: 0'); // 缓存控制
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); // 缓存控制
        header('Pragma: public'); // 缓存控制
        header('Content-Length: '.$fileSize); // 文件大小

        // 打开远程文件
        $file = fopen($url, 'rb'); // 以二进制模式打开文件

        // 分块下载并输出到浏览器
        while (!feof($file)) { // 判断文件是否已经读取完毕
            echo fread($file, $chunkSize); // 读取文件块
            ob_flush(); // 输出缓冲区
            flush(); // 输出缓冲区
        }

        fclose($file); // 关闭文件句柄
        exit(); // 终止脚本执行
    }

    protected static function getRemoteFileSize($url)
    {
        // 获取远程文件大小
        $headers = get_headers($url, true); // 获取 HTTP 响应头信息
        return isset($headers['Content-Length']) ? $headers['Content-Length'] : null; // 返回文件大小
    }



}
