<?php

namespace App\Libraries;



class LibSnowflake
{
    const EPOCH = 1543223810238;
    const WORKER_BITS = 5;
    const SEQUENCE_BITS = 12;
    const SEQUENCE_MAX = -1 ^ (-1 << self::SEQUENCE_BITS);
    const WORKER_MAX = -1 ^ (-1 << self::WORKER_BITS);
    const MAX_DUPLICATE_ATTEMPTS = 5;
    const TIMESTAMP_SHIFT = self::WORKER_BITS + self::SEQUENCE_BITS;

    private $redis;
    private $lockKey = 'snowflake_lock';
    private $idKey = 'snowflake_id';
    private $workerId;
    private $duplicateAttempts;

    public function __construct($workerId)
    {
        $redis = new Predis();
//        $redis = Redisd::getInstance();
        $this->redis = $redis;
        $this->workerId = $workerId;
        $this->duplicateAttempts = 0;
    }

    public function next()
    {
        // 获取分布式锁
        // $lockAcquired = $this->acquireLock();

        //  if ($lockAcquired) {
        try {
            // 生成雪花ID
            $id = $this->generateSnowflakeID();

            // 检查ID是否已存在
            if ($this->isIDExists($id)) {
                // ID已存在，重新生成
                $id = $this->next();

                // 增加重复尝试次数
                $this->duplicateAttempts++;

                // 如果连续生成重复ID的次数达到上限，抛出异常
                if ($this->duplicateAttempts >= self::MAX_DUPLICATE_ATTEMPTS) {
                    throw new \Exception('Failed to generate unique ID');
                }
            } else {
                // 重置重复尝试次数
                $this->duplicateAttempts = 0;
            }

            // 将ID标记为已存在
            $this->markIDAsExists($id);

            return $id;
        } finally {
            // 释放分布式锁
            $this->releaseLock();
        }
        //  }

        // 未能获取到锁，返回空值或抛出异常
        return null;
    }

    private function generateSnowflakeID()
    {
        $timestamp = $this->getTimestamp();
        $sequence = random_int(0, self::SEQUENCE_MAX);

        $id = ($timestamp << (self::WORKER_BITS + self::SEQUENCE_BITS)) |
            ($this->workerId << self::SEQUENCE_BITS) |
            $sequence;

        return $id;
    }

    private function getTimestamp()
    {
        return round(microtime(true) * 1000) - self::EPOCH;
    }

    private function isIDExists($id)
    {
        $key = $this->idKey . ':' . $id;
        return $this->redis->exists($key);
    }

    private function markIDAsExists($id)
    {
        $key = $this->idKey . ':' . $id;
        $this->redis->lock($key, 10);
    }

    private function releaseLock()
    {
        $this->redis->del($this->lockKey);
    }
    public function parseTimestamp($snowflakeId)
    {
        $timestamp = ($snowflakeId >> self::TIMESTAMP_SHIFT) + self::EPOCH;
        $milliseconds = $timestamp % 1000;
        $seconds = (int) ($timestamp / 1000);
        $date = date('Y-m-d H:i:s', $seconds);
        return $date . '.' . str_pad($milliseconds, 3, '0', STR_PAD_LEFT);
    }
}
