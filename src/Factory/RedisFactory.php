<?php
namespace TZ\Factory;

class RedisFactory
{
    /**
     * @return \Redis
     */
    public function getRedis(): \Redis
    {
        $redis = new \Redis();
        $redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));
        return $redis;
    }
}