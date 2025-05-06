<?php
/**
 * 公共类
 */
namespace App\Libraries;
use App\Exceptions\BusinessException;
use App\Libraries\Curl;
use CodeIgniter\Database\Exceptions\DatabaseException;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\Lang;
use Ramsey\Uuid\Uuid;

class Common {

    /**
     * 根据权限获取目录
     * @param $items
     * @return array
     */
    public function authorityTree($items){
        $index_items = [];
        foreach ($items as $item) {
            $index_items[$item['authority_id']] = $item;
        }
        $tree = array();
        foreach($index_items as $item){
            //判断是否有数组的索引==
            if(isset($index_items[$item['pid']])){     //查找数组里面是否有该分类  如 isset($items[0])  isset($items[1])
                $index_items[$item['pid']]['children'][] = &$index_items[$item['authority_id']]; //上面的内容变化,$tree里面的值就变化
            }else{
                $tree[] = &$index_items[$item['authority_id']];   //把他的地址给了$tree
            }
        }
        return $tree;
    }


    /**
     * Notes: 获取workid
     * Date: 2024/3/22 13:56
     * @return int
     */
    public static function getWorkerId(){
        $NUMWORKERBITS  = 10;
        $MAXWORKERID = (-1 ^ (-1 << $NUMWORKERBITS));
        $workerId = intval(microtime(true) * 1000);
        $workerId = ($workerId % ($MAXWORKERID + 1));  // 限制在合法范围内
        return $workerId;
    }


    /**
     * 生成guid
     * @return string
     */
    public static function getGuidv4($trim = true)
    {
        // Windows
        if (function_exists('com_create_guid') === true) {
            if ($trim === true)
                return strtoupper(trim(com_create_guid(), '{}'));
            else
                return strtoupper(com_create_guid());
        }

        // OSX/Linux
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
            return strtoupper(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
        }

        // Fallback (PHP 4.2+)
        mt_srand((double)microtime() * 10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);                  // "-"
        $lbrace = $trim ? "" : chr(123);    // "{"
        $rbrace = $trim ? "" : chr(125);    // "}"
        $guidv4 = $lbrace.
            substr($charid,  0,  8).$hyphen.
            substr($charid,  8,  4).$hyphen.
            substr($charid, 12,  4).$hyphen.
            substr($charid, 16,  4).$hyphen.
            substr($charid, 20, 12).
            $rbrace;
        return strtoupper($guidv4);
    }

    /**
     * Notes: 获取uuid
     * Date: 2024/3/22 13:57
     * @return string
     */
    public static function getUuid(){
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }

    //附加图片完整地址
    public static function getImageUrl($imageUrl){
        if (empty($imageUrl)) return '';

        if ((strpos($imageUrl,'http')) === false){
            $imageUrl = getenv('IMAGE_URL').$imageUrl;
        }
        return  $imageUrl;
    }


    /**
     * 二维数组或对象指定字段作为一维下标返回数组
     * @param $arr
     * @param string $field
     * @return array
     */
    public static function getKeyValueByField($arr,string $field = ''): array
    {
        if(empty($arr)) return [];
        if(empty($field)) return $arr;

        $list = [];
        foreach ($arr as $v){
            if(is_object($v)){
                $v = Response::objToArr($v);
            }
            if(isset($v[$field])){
                $list[$v[$field]] = $v;
            }
        }

        return $list;
    }

    /**
     * 二维数组或对象指定字段分组返回数组
     * @param $arr
     * @param string $field
     * @return array
     */
    public static function getArrByField($arr,string $field = ''): array
    {
        if(empty($arr)) return [];
        if(empty($field)) return $arr;

        $list = [];
        foreach ($arr as $v){
            if(is_object($v)){
                $v = Response::objToArr($v);
            }
            if(isset($v[$field])){
                $list[$v[$field]][] = $v;
            }
        }

        return $list;
    }

    /**
     * 检测isset返回的值
     * @param $fieldValue //返回的数据
     * @param $fieldInfo  //目标存在数组
     * @param $fields //获取的目标
     * @return void
     */
    public static function getIsSetValue(&$fieldValue,$fieldInfo,$fields): void
    {
        foreach ( $fields as $fk=>$fv ){
            if (is_numeric($fk)) $fk = $fv;
            if (isset($fieldInfo[$fv])) $fieldValue[$fk] = $fieldInfo[$fv];
        }
    }


    //二维数组指定多自动key分组
    public static function  groupByFields(array $list, array $fields,$separator='@*@')
    {
        if (empty($list)) return [];
        $ret = [];
        foreach ($list as $item) {
            $valueArr = [];
            foreach ($fields as $field) {
                if (!isset($item[$field])) {
                    throw new BusinessException("分组字段:{$field}未出现在列表中");
                }
                $v          = $item[$field];
                $valueArr[] = $v;
            }
            $ret[implode($separator, $valueArr)][] = $item;
        }

        return $ret;
    }


    //指定二维数组对应key,返回对应组合key的一维数组
    public static function  groupArrayColumnByFields(array $list, array $fields,$separator="")
    {
        if (empty($list)) return [];
        $ret = [];
        foreach ($list as $item) {
            $valueArr = [];
            foreach ($fields as $field) {
                if (!isset($item[$field])) {
                    throw new BusinessException("分组字段:{$field}未出现在列表中");
                }
                $v          = $item[$field];
                $valueArr[] = $v;
            }
            $ret[implode($separator, $valueArr)] = $item;
        }

        return $ret;
    }


    /**
     * 按指定key字段分组，返回二维数据
     * @param $list
     * @param $key
     * @return array
     */
    public static function arrayGroupByKey($list,$key){

        if (empty($list)) return [];
        $listGroup = [];
        foreach ( $list as $row ){
            if (!isset($row[$key])) throw new BusinessException(Lang::get('Errors.10100004',[$key]));
            $listGroup[$row[$key]][] = $row;
        }
        return $listGroup;
    }

    /**
     * 返回指定key的field
     * @param $list
     * @param $key
     * @param $field
     * @return array
     */
    public static function arrayGroupFieldByKey($list,$key,$field){

        $listGroup = [];
        foreach ( $list as $row ){
            $listGroup[$row[$key]][] = $row[$field] ?? '';
        }
        return $listGroup;
    }

    /**
     * 比较两个数组的差异,明细日志
     *
     * @param array $oldArray 旧数组
     * @param array $newArray 新数组
     * @param array $keys 需要比较的键值
     * @param array $fields 需要比较的字段
     * @param array $mapping 映射参数数组，用于生成唯一值的映射值名称
     * @return string 返回包含删除、新增和修改的元素的字符串
     */
    public static function compareArrays($oldArray, $newArray, $keys, $fields,$mapping)
    {
        $deleted = [];
        $added = [];
        $modified = [];

        // 旧数组中的元素是否存在于新数组中
        foreach ($oldArray as $oldItem) {
            $oldKeys = array_intersect_key($oldItem, array_flip($keys));
            $key = array_values($oldKeys);
            $unionValue =  array_shift($key);
            // 查找新数组中是否存在相同键值的元素
            $newItem = array_filter($newArray, function ($newItem) use ($oldKeys, $keys) {
                $newKeys = array_intersect_key($newItem, array_flip($keys));
                return $oldKeys == $newKeys;
            });

            // 如果不存在相同键值的元素，则将旧元素添加到删除数组中
            if (empty($newItem)) {
                $deleted[] = $oldItem;
            } else {
                // 如果存在相同键值的元素，则比较两个元素之间的差异
                $newItem = reset($newItem);
                $oldFields = array_intersect_key($oldItem, array_flip($fields));
                $newFields = array_intersect_key($newItem, array_flip($fields));

                $diff = [];
                foreach ($oldFields as $field => $oldValue) {
                    if (!self::compareValue($oldValue, $newFields[$field])) {
                        $diff[$field] = ['old' => $oldValue, 'new' => $newFields[$field]];
                    }
                }

                if(!empty($diff)){
                    $modified[] = [
                        'keys' => $oldKeys,
                        'old' => $oldItem,
                        'new' => $newItem,
                        'diff' => $diff
                    ];
                }

            }

        }

        // 新数组中的元素是否存在于旧数组中
        foreach ($newArray as $newItem) {
            $newKeys = array_intersect_key($newItem, array_flip($keys));
            // 查找旧数组中是否存在相同键值的元素
            $oldItem = array_filter($oldArray, function ($oldItem) use ($newKeys, $keys) {
                $oldKeys = array_intersect_key($oldItem, array_flip($keys));
                return $newKeys == $oldKeys;
            });

            // 如果不存在相同键值的元素，则将新元素添加到新增数组中
            if (empty($oldItem)) {
                $added[] = $newItem;
            }
        }
        return self::getCompareContent($deleted,$added,$modified,$mapping,$keys);
    }

    public static function compareValue($value1, $value2) {
        // 如果两个值都是数字（包括字符串形式的数字和浮点数），则进行浮点数比较
        if ((is_numeric($value1) || is_float($value1)) && (is_numeric($value2) || is_float($value2))) {
            if (is_string($value1) && strpos($value1, '.') === false) {
                $value1 = (int)$value1;  // 将纯整数字符串转换为整数
            } else {
                $value1 = (float)$value1;  // 转换为浮点数
            }

            if (is_string($value2) && strpos($value2, '.') === false) {
                $value2 = (int)$value2;  // 将纯整数字符串转换为整数
            } else {
                $value2 = (float)$value2;  // 转换为浮点数
            }

            return abs($value1 - $value2) < 0.0001;  // 设置一个允许的误差范围
        }

        // 默认情况下，执行严格比较
        return $value1 === $value2;
    }

    public static function getCompareContent($deleted,$added,$modified,$mapping,$keys){
        $result  = '';
        // 生成删除的元素字符串
        if (!empty($deleted)) {
            $result .= "删除,";
            foreach ($deleted as $item) {
                $unionKeys = array_intersect_key($item, array_flip($keys));
                $key = array_values($unionKeys);
                $unionValue =  array_shift($key);
                $result.="{$unionValue}:";
                foreach($item as $key =>$val){
                    if(isset($mapping[$key])){
                        $result .= "{$mapping[$key]}：[{$val}];";
                    }
                }
            }
            $result.= "\n";
        }


        // 生成新增的元素字符串
        if (!empty($added)) {
            $result .= "新增,";
            foreach ($added as $item) {
                $unionKeys = array_intersect_key($item, array_flip($keys));
                $key = array_values($unionKeys);
                $unionValue =  array_shift($key);
                $result.="{$unionValue}:";
                foreach($item as $key =>$val){
                    if(isset($mapping[$key])){
                        $result .= "{$mapping[$key]}：[{$val}];";
                    }
                }
            }
            $result.= "\n";
        }

        // 生成修改的元素字符串
        if (!empty($modified)) {
            $result .= "修改,";
            foreach ($modified as $item) {
                $key = array_values($item['keys']);
                $unionValue =  array_shift($key);
                $result .= "{$unionValue}:";
                $diffValues = [];
                foreach ($item['diff'] as $field => $diff) {
                    if(isset($mapping[$field]) && $mapping[$field]){
                        $diffValues[] = "{$mapping[$field]}：[{$diff['old']}]~[{$diff['new']}]";
                    }
                }
                $result .= implode('；', $diffValues) . ";";
            }
        }

        return $result;
    }

    /**
     * 不同时区的时间互相转换
     * @param string $datetime 时间，如：2023-01-01 01:01:01
     * @param string $format 格式，如：Y-m-d H:i:s
     * @param string $fromZone 原时区
     * @param string $toZone 转换后的时区
     * @return string
     */
    public static function convertDataTimeZone(string $datetime, string $format = '', string $fromZone = '', string $toZone = ''): string
    {
        if (empty($format)) {
            $format = 'Y-m-d H:i:s';
        }
        if (empty($fromZone)) {
            $fromZone = 'Asia/Shanghai';
        }
        if (empty($toZone)) {
            $toZone = 'Asia/Shanghai';
        }
        if ($fromZone == $toZone) {
            return $datetime;//时区相同，不需要转换
        }
        try{
            $dateTime = new \DateTime($datetime, new \DateTimeZone($fromZone));
            $dateTime->setTimezone(new \DateTimeZone($toZone));
            return $dateTime->format($format);
        }catch (\Exception $e){
            return $datetime;//如果转换失败，原样返回
        }
    }

    /**
     * 去除两边的空格
     * @return void
     */
    public static function RemoveSpaces($str){
        $str = mb_ereg_replace('^(　| )+', '', $str);
        $str = mb_ereg_replace('(　| )+$', '', $str);
        return mb_ereg_replace('　　', "\n　　", $str);
    }
}
