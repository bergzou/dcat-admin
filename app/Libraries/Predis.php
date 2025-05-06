<?php
/**
 * @Notes: Redis 工具类
 * @Date: 2024/3/15
 * @Time: 15:53
 * @Interface Predis
 * @return
 */
namespace App\Libraries;
use Illuminate\Redis\Connections\Connection;


use Illuminate\Support\Facades\Redis;

class Predis{

    private $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }


    public function set($key,$value)
    {
        return Redis::set($key,$value);
    }

    /**
     * 判断key是否存在
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        return Redis::exists($key);
    }

    /**
     * 删除
     * @param string $key
     * @return string
     */
    public function del($key)
    {
        return Redis::del($key);
    }

    /**
     * 查找键值
     */
    public function keys($pattern)
    {
        return Redis::keys($pattern);
    }



    /**
     * 获取指定 key 的值
     * @param string $key 键值
     * @return mixed
     */
    public function get($key)
    {
        return Redis::get($key);
    }

    /**
     * 获取所有(一个或多个)给定 key 的值
     * @param string $key 键值
     * @return mixed
     */
    public function mget($array)
    {
        return Redis::mget($array);
    }

    /**
     * 将成员值增加给定的量。
     */
    public function Incr($key)
    {
//        $key = $this->prefix.$key;
        return Redis::incr($key);
    }

    #endregion

    #region 哈希相关方法（hash）

    /**
     * 设置一个哈希值，存在则返回false
     */
    public function hSet( $key, $hashKey, $value)
    {
        return Redis::hSet( $key, $hashKey, $value );
    }

    /**
     * 当对应哈希值不存在时，才设置一个哈希值
     */
    public function hSetNx( $key, $hashKey, $value )
    {
        return Redis::hSetNx( $key, $hashKey, $value );
    }

    /**
     * 填充整个哈希。
     */
    public function hMSet( $key, $hashKeys, $expire = 86400)
    {
        $rst = Redis::hMSet( $key, $hashKeys );
        if ($expire > 0) {
            Redis::expire($key,$expire);
        }
        return $rst;
    }

    /**
     * 更新过期时间
     */
    public function expire($key,$expire=86400)
    {
        return Redis::expire($key,$expire);
    }

    /**
     * 获取一个哈希值
     */
    public function hGet($key, $hashKey)
    {
        return Redis::hGet($key, $hashKey);
    }

    /**
     *
     */
    public function hMGet( $key, $hashKeys )
    {
        return Redis::hMGet( $key, $hashKeys );
    }

    /**
     * 删除哈希值
     * 如果哈希表不存在，或者键不存在，则返回false。
     */
    public function hDel( $key, $hashKey)
    {
        return Redis::hDel( $key, $hashKey);
    }

    /**
     * 以字符串数组的形式返回哈希中的键。
     */
    public function hKeys( $key )
    {
        return Redis::hKeys( $key );
    }

    /**
     * 以字符串数组的形式返回哈希中的值。
     */
    public function hVals( $key )
    {
        return Redis::hVals( $key );
    }

    /**
     * 以字符串索引的字符串数组形式返回整个哈希。
     */
    public function hGetAll( $key )
    {
        return Redis::hGetAll( $key );
    }

    /**
     * 验证键中是否存在指定的成员。
     */
    public function hExists( $key, $hashKey )
    {
        return Redis::hExists( $key, $hashKey );
    }

    /**
     * 将哈希中的成员值增加给定的量。
     */
    public function hIncrBy( $key, $hashKey, $value )
    {
        return Redis::hIncrBy( $key, $hashKey, $value );
    }

    /**
     * 将一个或多个值插入到列表头部。 如果 key 不存在，一个空列表会被创建并执行 LPUSH 操作。 当 key 存在但不是列表类型时，返回一个错误
     * @param $key
     * @param $value
     * @return int 执行 LPUSH 命令后，列表的长度
     */
    public function lPush($key, $value)
    {
        return Redis::lPush($key, $value);
    }

    /**
     * 将一个或多个值插入到已存在的列表头部，列表不存在时操作无效
     * @param $key
     * @param $value
     * @return int LPUSHX 命令执行之后，列表的长度
     */
    public function lPushx($key, $value)
    {
        return Redis::lPushx($key, $value);
    }

    /**
     * 将一个或多个值插入到列表的尾部(最右边)。如果列表不存在，一个空列表会被创建并执行 RPUSH 操作
     * @param $key
     * @param $value
     * @return int 执行 RPUSH 操作后，列表的长度
     */
    public function rPush($key, $value,$expire=86400)
    {
        Redis::rPush($key, $value);
        if ($expire > 0) {
            Redis::expire($key,$expire);
        }
        return true;
    }

    /**
     * 将一个或多个值插入到已存在的列表尾部(最右边)。如果列表不存在，操作无效。
     * @param $key
     * @param $value
     * @return int 执行 Rpushx 操作后，列表的长度
     */
    public function rPushx($key, $value)
    {
        return Redis::rPushx($key, $value);
    }

    /**
     * 移除并返回列表的第一个元素
     * @param string $key
     * @return string 列表的第一个元素。 当列表 key 不存在时，返回 null
     */
    public function lPop($key)
    {
        return Redis::lPop($key);
    }

    /**
     * 用于移除并返回列表的最后一个元素
     * @param string $key
     * @return string 列表的最后一个元素。 当列表不存在时，返回 nil
     */
    public function rPop($key)
    {
        return Redis::rPop($key);
    }

    /**
     * 移出并获取列表的第一个/n个元素
     * @param $key
     * @return array
     */
    public function blPop($keys,$timeout)
    {
        return Redis::blPop($keys,$timeout);
    }

    /**
     * 移出并获取列表的最后一个/n个元素
     * @param $key
     * @return array
     */
    public function brPop($keys,$timeout)
    {
        return Redis::brPop($keys,$timeout);
    }

    /**
     * 返回列表的长度
     * @param string $key
     * @return int
     */
    public function lLen($key)
    {
        return Redis::lLen($key);
    }

    /**
     * 返回列表中指定区间内的元素
     * @param $key
     * @param $start
     * @param $end
     * @return array
     */
    public function lRange($key, $start,$end)
    {
        return Redis::lRange($key, $start,$end);
    }

    /**
     * Notes: 根据参数 COUNT 的值，移除列表中与参数 VALUE 相等的元素
     * Date: 2024/3/19 20:01
     * @param $key
     * @param $value
     * @param $count
     * @return mixed
     */
    public function lRem($key, $value, $count)
    {
        return Redis::lRem($key, $value, $count);
    }

    /**
     * Notes: 通过索引来设置元素的值
     * Date: 2024/3/19 20:01
     * @param $key
     * @param $index
     * @param $value
     * @return mixed
     */
    public function lSet($key, $index, $value)
    {
        return Redis::lSet($key, $index, $value);
    }

    /**
     * Notes: 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除
     * Date: 2024/3/19 20:01
     * @param $key
     * @param $start
     * @param $stop
     * @return mixed
     */
    public function lTrim($key, $start, $stop)
    {
        return Redis::lTrim($key, $start, $stop);
    }

    /**
     * Notes: 通过索引获取列表中的元素。你也可以使用负数下标，以 -1 表示列表的最后一个元素，
     * -2 表示列表的倒数第二个元素，以此类推
     * Date: 2024/3/19 20:01
     * @param $key
     * @param $index
     * @return mixed
     */
    public function lIndex($key, $index)
    {
        return Redis::lIndex($key, $index);
    }

    /**
     * Notes: 在列表的元素前或者后插入元素
     * Date: 2024/3/19 20:01
     * @param $key
     * @param $position
     * @param $pivot
     * @param $value
     * @return mixed
     */
    public function lInsert($key, $position, $pivot, $value)
    {
        return Redis::lInsert($key, $position, $pivot, $value);
    }

    /**
     * Notes: 移除列表的最后一个元素，并将该元素添加到另一个列表并返回
     * Date: 2024/3/19 20:01
     * @param $key
     * @param $position
     * @param $pivot
     * @param $value
     * @return mixed
     */
    public function rpoplpush($key, $position, $pivot, $value)
    {
        return Redis::rpoplpush($key, $position, $pivot, $value);
    }

    /**
     * Notes: 从列表中弹出一个值，将弹出的元素插入到另外一个列表中并返回它；
     *  如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为
     * Date: 2024/3/19 20:00
     * @param $key
     * @param $position
     * @param $pivot
     * @param $value
     * @return mixed
     */
    public function brpoplpush($key, $position, $pivot, $value)
    {
        return Redis::brpoplpush($key, $position, $pivot, $value);
    }

    /**
     * Notes: 向无序集合添加成员
     * Date: 2024/3/19 20:00
     * @param $key
     * @param $value
     * @return mixed
     */
    public function sAdd($key, $value)
    {
        return Redis::sAdd($key, $value);
    }

    /**
     * Notes: 移除集合中的一个或多个成员元素
     * Date: 2024/3/19 20:00
     * @param $key
     * @param $member
     * @return mixed
     */
    public function sRem($key, $member)
    {
        return Redis::sRem($key, $member);
    }

    /**
     * Notes: 取出无序集合中的所有成员
     * Date: 2024/3/19 20:00
     * @param $key
     * @return mixed
     */
    public function sMembers($key)
    {
        return Redis::sMembers($key);
    }

    /**
     * Notes: 判断成员是否在无序集合中
     * Date: 2024/3/19 19:59
     * @param $key
     * @param $value
     * @return mixed
     */
    public function sIsMember($key, $value)
    {
        return Redis::sIsMember($key,$value);
    }

    /**
     * Notes: 向有序集合添加一个或多个成员，或者更新已存在成员的分数
     * Date: 2024/3/19 19:59
     * @param $key
     * @param $score
     * @param $value
     * @return mixed
     */
    public function zAdd($key, $score, $value)
    {
        return Redis::zAdd($key, $score, $value);
    }

    /**
     * Notes: 移除有序集合中的一个或多个成员
     * Date: 2024/3/19 19:59
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zRem($key, $member)
    {
        return Redis::zRem($key, $member);
    }

    /**
     * Notes: 通过索引区间返回有序集合指定区间内的成员
     * Date: 2024/3/19 19:59
     * @param $key
     * @param $start
     * @param $end
     * @param $withscores
     * @return mixed
     */
    public function zRange($key, $start, $end, $withscores = null)
    {
        return Redis::zRange($key, $start, $end, $withscores);
    }

    /**
     * Notes: 获取有序集合的成员数
     * Date: 2024/3/19 19:59
     * @param $key
     * @return mixed
     *
     */
    public function zCard($key)
    {
        return Redis::zCard($key);
    }

    /**
     * Notes: 返回有序集合中指定成员的索引
     * Date: 2024/3/19 19:59
     * @param $key
     * @param $member
     * @return mixed
     */
    public function zRank($key, $member)
    {
        return Redis::zRank($key, $member);
    }

    /**
     * Notes:计算在有序集合中指定区间分数的成员数
     * Date: 2024/3/19 19:58
     * @param $key
     * @param $start
     * @param $end
     * @return mixed
     */
    public function zCount($key, $start, $end)
    {
        return Redis::zCount($key, $start, $end);
    }

    /**
     * 加锁
     * @param $key
     * @param int $expire
     * @param string $lockValue
     * @return  bool
     */
    public function lock($key,$expire=10,$lockValue='1'){
        try {
            $res = Redis::setnx($key, $lockValue);
            if ($res) {
                Redis::expire($key, $expire);
            }
            return $res;
        }catch (\Throwable $e){
            usleep(100);
            try {
                $res = Redis::setnx($key, $lockValue);
                if ($res) {
                    Redis::expire($key, $expire);
                }
                return $res;
            }catch (\Throwable $e){
                //这里需加日志
                return true;
            }
        }
    }
}

