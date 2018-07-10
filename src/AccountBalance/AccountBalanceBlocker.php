<?php
namespace TZ\AccountBalance;

class AccountBalanceBlocker
{
    const EXPIRE=60;

    /**
     * @var \Redis
     */
    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function block(string $userId): void
    {
        $this->redis->setex($this->getKey($userId), self::EXPIRE, '');
    }

    public function unblock(string $userId): void
    {
        $this->redis->del($this->getKey($userId));
    }

    public function isBlocked(string $userId): bool
    {
        return $this->redis->exists($this->getKey($userId));
    }

    private function getKey(string $userId): string
    {
        return sprintf('user.account.balance.%d', $userId);
    }
}
