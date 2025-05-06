<?php
namespace App\Libraries;

class GetNumber
{

    protected $key = '';
    protected $prefix = '';
    protected $time = '';
    protected $symbol = ''; #连接符号
    protected $fillLength = 4; #填充位数
    protected $padString = '0'; #填充值
    protected $serviceId = ''; #服务id


    private function __construct(){
    }

    public static function getStatic(){
        return new static();
    }

    //缓存key
    public function setKey($key){
        $this->key = $key;
        return $this;
    }

    //设置前缀
    public function setPrefix($prefix){
        $this->prefix = $prefix;
        return $this;
    }

    public function setSymbol($symbol=''){
        $this->symbol = $symbol;
        return $this;
    }

    //编号自动补充位数
    public function setFillLength($length=4){
        $this->fillLength = $length;
        return $this;
    }

    //设置时间
    public function setTime($time=''){

        if (is_bool($time)){
            if ($time){ #$time = true
                $time = date('Ymd');
            }else{ #$time = false
                $time = '';
            }
        }
        $this->time = $time;
        return $this;
    }

    public function setServiceId($serviceId=''){
        $this->serviceId = $serviceId;
        return $this;
    }


    /**
     * @功能:获取单个单号
     * @版本：V1.0
     * @作者: lzl
     * @日期: 2023-07-10 11:45:31
     * @return string
     */
    public function getCode()
    {

        $codeId = $this->getId();
        $codeId = str_pad($codeId, $this->fillLength, $this->padString, STR_PAD_LEFT);
        $codeArr[] = $this->prefix;
        if ($this->time) $codeArr[] = $this->time;
        $codeArr[] = $codeId;

        return implode($this->symbol,$codeArr);
    }

    /**
     * @功能:获取批量单号
     * @版本：V1.0
     * @作者: lzl
     * @日期: 2023-07-10 11:45:54
     * @return void
     */
    public function getBatchCode(){

    }



    /**
     * @功能:获取后缀id
     * @版本：V1.0
     * @作者: lzl
     * @日期: 2023-07-10 11:42:41
     * @return string
     */
    private function getSequence()
    {
        $num_str = str_pad($this->getId(), $this->fillLength, '0', STR_PAD_LEFT) . Common::getUuid();
        return $num_str;
    }


    /**
     * @功能:获取单个id
     * @版本：V1.0
     * @作者: lzl
     * @日期: 2023-07-10 11:29:59
     * @return bool|int
     */
    private function getId()
    {
        $serviceId = empty($this->serviceId) ? getenv('SERVICE_ID') : $this->serviceId;
        $key = 'getNumber:'.$serviceId.":".$this->key;

        $redis = (new Predis());
        if(!empty($this->time)){
            $key .= ":".date('Ymd');
            $id = $redis->Incr($key);
            if(!$redis->exists($key)){
                $remainingTime = strtotime('tomorrow') - time();
                $redis->expire($key,$remainingTime+10);
            }
        }else{
            $id = $redis->Incr($key);
        }

        return $id;
    }

    /**
     * @功能:获取批量id
     * @版本：V1.0
     * @作者: lzl
     * @日期: 2023-07-10 11:54:59
     * @return void
     */
    private  function getBatchId(){
    }

}

