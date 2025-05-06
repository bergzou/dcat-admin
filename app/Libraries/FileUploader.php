<?php
/**
 * 文件上传
 */
namespace App\Libraries;



class FileUploader
{
    /**
     * 上传文件
     *
     * @param  object  $file                上传的文件对象
     * @param  string  $destination         上传文件的目标路径
     * @param  int     $maxSize             允许上传的最大文件大小（单位：KB）
     * @param  array   $allowedExtensions   允许上传的文件后缀名（默认为doc）
     * @return array                        上传结果（数组格式），包含两个元素：
     *                                      ack 第一个元素表示上传是否成功，true表示上传成功，false表示上传失败；
     *                                      newFileName 第二个元素表示上传成功时的新文件名，上传失败时的错误信息。
     *                                      message 上传文件失败的
     */
    public static  function uploadFile($file, $destination, $maxSize = 2048, $allowedExtensions = array('doc'),$newFileName='')
    {
        $return = [
            'ack' => false,
            'message' => '文件上传失败！',
            'newFileName' => $newFileName,
        ];
        // 验证文件是否上传成功
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $return;
        }

        // 获取文件信息
        $fileName = $file->getName(); // 获取文件名
        $fileSize = $file->getSize(); // 获取文件大小
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // 获取文件后缀名

        // 验证文件大小
        if ($fileSize > $maxSize * 1024) {
            $return['message'] = '文件大小超过了允许的最大值！';
            return $return;
        }

        // 验证文件后缀名
        if (!in_array($fileExt, $allowedExtensions)) {
            $return['message'] = '文件类型不允许！';
            return $return;
        }

        // 根据文件后缀名判断允许上传的文件类型
        switch ($fileExt) {
            case 'doc':
            case 'docx':
                $allowedTypes = array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                break;
            case 'xls':
            case 'xlsx':
                $allowedTypes = array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                break;
            case 'ppt':
            case 'pptx':
                $allowedTypes = array('application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
                break;
            case 'pdf':
                $allowedTypes = array('application/pdf');
                break;
            case 'jpg':
            case 'jpeg':
                $allowedTypes = array('image/jpeg');
                break;
            case 'png':
                $allowedTypes = array('image/png');
                break;
            case 'gif':
                $allowedTypes = array('image/gif');
                break;
            default:
                return array(false, '文件类型不允许！');
        }

        // 验证文件真实类型
        $fileType = $file->getMimeType(); // 获取文件的真实类型
        if (!in_array($fileType, $allowedTypes)) {
            $return['message'] = '文件类型不正确！';
            return $return;
        }

        if(empty($newFileName)){
            // 生成新的文件名，并移动文件到指定目录
            $newFileName = md5(uniqid(mt_rand())) . '.' . $fileExt; // 生成新的文件名
            if (!$file->move($destination,$newFileName)) { // 移动文件到目标路径
                $return['message'] = '文件上传失败！';
                return $return;
            }
        }


        $return['ack'] = true;
        $return['message'] = '上传成功';
        $return['newFileName'] = $newFileName;
        return $return; // 返回上传结果
    }
}
