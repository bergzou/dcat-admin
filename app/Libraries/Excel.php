<?php
/**
 * @Notes: Excel 工具类
 * @Date: 2024/3/15
 * @Time: 18:22
 * @Interface ExcelTool
 * @return
 */


namespace App\Libraries;



use Illuminate\Support\Facades\Lang;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Excel
{
    /**
     * @var array 存储错误信息
     */
    protected $errors = [];

    /**
     * 导入
     *
     * @param string $file 文件路径
     * @param array $requiredColumns 导入的列名
     * @param array $columnMappings 需要映射的导入列名
     * @return array|false
     * @throws Exception
     */
    public function import($file, $requiredColumns, $columnMappings = [], $headerLine = 1)
    {
        // 创建读取器对象并加载文件
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);

        //获取活动工作表和标题行
        $worksheet = $spreadsheet->getActiveSheet();
        $header = $worksheet->rangeToArray('A' . $headerLine . ':' . $worksheet->getHighestColumn() . $headerLine, null, true, true, true)[1];

        // 将标题行转换为小写
        //$header = $header;

        // 如果提供了翻译，请翻译列名
        $translatedColumns = array_map(function ($column) use ($columnMappings) {
            return $columnMappings[$column] ?? $column;
        }, $header);

        $compare_array = array_values($header);
        $missingColumns = array_merge(array_diff($requiredColumns, $compare_array), array_diff($compare_array, $requiredColumns));

        if (count($missingColumns) > 0) {
            foreach ($missingColumns as $missingColumn) {
                //目前没设置缓存 只能先注释掉
                $this->errors[] = $missingColumn;
            }

            return false;
        }


        // 将工作表转换为行数组
        $rows = $worksheet->toArray(null, true, true, true);

        //删除标题行
        array_shift($rows);

        // 将列映射到数据库字段
        return array_map(function ($row) use ($columnMappings) {
            return array_combine($columnMappings, $row);
        }, $rows);
    }


    /**
     * 导出Excel文件
     *
     * @param array $data 要导出的数据，数组格式
     * @param array $headers 表头，数组格式，每个元素包含两个属性：label和field，分别表示表头名称和对应的数据库字段名
     * @param string $filename 导出的Excel文件名
     * @param array $columnWidth 自定义列的宽度，数组格式，key为列的字母，value为列的宽度，单位为像素
     * @return void
     */
    public function export($data, $headers, $filename = 'export.xlsx', $columnWidth = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header['label']);
            $col++;
        }

        // 填充数据
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($headers as $header) {
                $fieldValue = $item[$header['field']] ?? '';
                $sheet->setCellValue($col . $row, $fieldValue);
                $col++;
            }
            $row++;
        }

        // 设置列宽
        foreach ($headers as $header) {
            $col = array_search($header, $headers) + 1;
            $colLetter = chr(64 + $col);
            if (isset($columnWidth[$colLetter])) {
                $sheet->getColumnDimension($colLetter)->setWidth($columnWidth[$colLetter]);
            }
//            else {
//                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
//            }
        }

        // 输出Excel文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }


    /**
     * 获取错误
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 导出CSV文件
     *
     * @param array $data 要导出的数据，二维数组格式
     * @param string $filename 导出的文件名
     * @param array $headers CSV文件的列头，映射数据库字段，一维数组格式，例如：['id' => 'ID', 'name' => '姓名', 'age' => '年龄']
     */
    public static function exportCsv($data, $filename = 'export.csv', $headers = [])
    {
        // 打开输出缓冲区，将CSV文件内容写入到缓冲区
        ob_start();
        $file = fopen('php://output', 'w');

        // 写入列头
        if (!empty($headers)) {
            fputcsv($file, array_values($headers));
        }

        // 写入数据
        foreach ($data as $row) {
            // 根据映射关系生成行数据
            $rowData = [];
            foreach ($headers as $key => $value) {
                $rowData[] = $row[$key];
            }
            fputcsv($file, $rowData);
        }

        // 关闭文件
        fclose($file);

        // 从输出缓冲区获取CSV文件内容，并清空缓冲区
        $content = ob_get_clean();

        // 设置HTTP头，告诉浏览器下载一个CSV文件
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // 输出CSV文件内容到浏览器
        echo $content;
        exit();
    }

    /**
     * 多sheet的导出
     * @param [type] $data_array
     * @param [type] $name
     * @return void
     * @author zm 2023年5月4日15:53:57
     */
    public function xtexport($data_array, $name)
    {
        // $name = '团长' . date("Y-m-d", time());
        $spreadsheet = new Spreadsheet();
        foreach ($data_array as $key => $data) {
            $this->opSheet($spreadsheet, $key, $data);
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        //删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    }

    /**
     * 处理多sheet
     * @param [type] $spreadsheet
     * @param [type] $n
     * @param [type] $data
     * @return void
     * @author zm 2023年5月4日15:53:46
     */
    public function opSheet($spreadsheet, $n, $data)
    {
        $spreadsheet->createSheet();//创建sheet
        $objActSheet = $spreadsheet->setActiveSheetIndex($n);//设置当前的活动sheet
        $keys = $data['rows'][0];//这是你的数据键名
        $count = count($keys);//计算你所占的列数
        $infoStart = 1;//下面的详细信息的开始行数
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        $sheet = $spreadsheet->getActiveSheet($n)->setTitle($data['title']);//设置sheet的名称
        // $spreadsheet->getActiveSheet($n)->mergeCells('A1:' . $cellName[$count - 1] . '1'); //合并单元格
        // $spreadsheet->getActiveSheet($n)->getStyle('A1')->getFont()->setSize(20); //设置title的字体大小
        // $spreadsheet->getActiveSheet($n)->getStyle("$infoStart")->getFont()->setBold(true); //标题栏加粗
        // $objActSheet->setCellValue('A1', $data['title']); //设置每个sheet中的名称title


        /**
         * 图中最下面的数据信息循环
         */
        foreach ($data['rows'] as $key => $item) {
            //循环设置单元格：
            //$key+$infoStart,因为第一行是表头，所以写到表格时   从第数据行开始写
            for ($i = 65; $i < $count + 65; $i++) {
                //数字转字母从65开始：
                //$sheet->setCellValue(strtoupper(chr($i)) . ($key + "$infoStart"), $item[[$keys][$i - 65]]);
                $sheet->setCellValue(strtoupper(chr($i)) . ($key + "$infoStart"), $item[$i - 65]);
                $spreadsheet->getActiveSheet($n)->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
            }
        }
    }


    /**
     * 导出Excel文件并上传到远程服务器
     *
     * @param array $data 要导出的数据，数组格式
     * @param array $headers 表头，数组格式，每个元素包含两个属性：label和field，分别表示表头名称和对应的数据库字段名
     * @param string $filename 导出的Excel文件名
     * @param array $columnWidth 自定义列的宽度，数组格式，key为列的字母，value为列的宽度，单位为像素
     * @param string $uploadUrl 上传到的远程服务器地址
     * @return mixed CURL 请求的响应结果
     */
    public function exportAndUploadExcel($data, $headers, $filename, $columnWidth = array())
    {

        $result = ['code' => 500, 'msg' => Lang::get('web.50001'), 'url' => ''];
        $filePath = storage_path('app/public/temp/excel/') . $filename;
        // 创建 Excel 对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置 Excel 属性
        $spreadsheet->getProperties()
            ->setCreator('Your Name')
            ->setTitle($filename)
            ->setSubject($filename);
        // 设置表头
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header['label']);
            if (isset($columnWidth[$col])) {
                $sheet->getColumnDimension($col)->setWidth($columnWidth[$col] / 7);
            }
            $col++;
        }

        // 填充数据
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValueExplicit($col . $row, $item[$header['field']], DataType::TYPE_STRING, NumberFormat::FORMAT_TEXT);
                $col++;
            }
            $row++;
        }
        // 设置文件名并输出 Excel 文件
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // 上传 Excel 文件到远程服务器
        $response = (new Curl())->curlUpload($filePath);

        $response = json_decode($response, true);

        if (!empty($response)) {
            if ($response['isSuccess']) {
                $result['url'] = $response['data']['objectUrl'];
                $result['msg'] = $response['msg'];
                $result['code'] = 200;
            } else {
                $response['code'] = 500;
                $response['msg'] = $response['msg'];
            }

        }


        // 删除临时文件
        unlink($filePath);

        // 返回 CURL 请求的响应结果
        return $result;
    }

    /**
     * 导入
     *
     * @param string $file 文件路径必须在uploads下
     * @param array $requiredColumns 导入的列名
     * @param array $columnMappings   需要映射的导入列名
     * @return array|false
     */
    public static function xlswriterImport($file,$requiredColumns, $columnMappings = []){
        $filePath = storage_path('app/public/temp/excel');
        $config   = ['path' => $filePath];
        $excel =  new \Vtiful\Kernel\Excel($config);
        // 读取测试文件
        $excel->openFile($file)
            ->openSheet()
            ->setType([\Vtiful\Kernel\Excel::TYPE_STRING]);
        // 如果使用【!=】进行判断，出现空行时，返回空数组，将导致读取中断；
        $data = [];
        $i = 0;
        $header = [];

        while (($row = $excel->nextRow()) !== NULL) {

            if($i == 0){
                $header = $row;
            }else{
                if(empty(array_filter($row))){
                    continue;
                }
                $data[] = $row;
            }
            $i++;
        }
        // 将标题行转换为小写
        //$header = $header;

        // 如果提供了翻译，请翻译列名
        $translatedColumns = array_map(function ($column) use ($columnMappings) {
            return isset($columnMappings[$column]) ? $columnMappings[$column] : $column;
        }, $header);

        $compare_array = array_values($header);

        $missingColumns =array_merge(array_diff($requiredColumns, $compare_array),array_diff($compare_array,$requiredColumns));
        if (count($missingColumns) > 0) {
            foreach ($missingColumns as $missingColumn) {
                //目前没设置缓存 只能先注释掉
                // $this->errors[] = lang('Column not found: {0}', [$missingColumn]);
            }
            return false;
        }

        // 将列映射到数据库字段
        $mappedRows = array_map(function ($row) use ($columnMappings) {
            if(!empty($row)){
                foreach ($row as &$rom_m){
                    $rom_m = Common::RemoveSpaces($rom_m);
                }
                if(count($columnMappings) != count($row)){
                    return false;
                }
                return array_combine($columnMappings, $row);
            }
        }, $data);
        return $mappedRows;
    }

    public function sum($file){
        $filePath = storage_path('app/public/temp/excel');
        $config   = ['path' => $filePath];
        $excel =  new \Vtiful\Kernel\Excel($config);
        // 读取测试文件
        $excel->openFile($file)
            ->openSheet();
        // 如果使用【!=】进行判断，出现空行时，返回空数组，将导致读取中断；
        $i = 0;
        while (($row = $excel->nextRow()) !== NULL) {
            $i++;
        }
        return $i;
    }

}
