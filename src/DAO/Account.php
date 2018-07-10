<?php
namespace TZ\DAO;

use Money\Money;

class Account
{/**
     * @var int
     */
    private $userId;

    /**
     * @var Money
     */
    private $amount;

    public function __construct(int $userId, Money $amount)
    {
        $this->userId = $userId;
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @param Money $amount
     */
    public function setAmount(Money $amount): void
    {
        $this->amount = $amount;
    }
}
