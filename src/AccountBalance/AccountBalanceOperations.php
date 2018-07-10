<?php
namespace TZ\AccountBalance;

use Money\Money;
use TZ\DAO\Account;
use TZ\Exception\NotEnoughMoneyException;

class AccountBalanceOperations {
    /**
     * @param Account $account
     * @param Money $money
     */
    public function add(Account $account, Money $money): void
    {
        $account->setAmount($account->getAmount()->add($money));
    }

    /**
     * @param Account $account
     * @param Money $money
     * @throws NotEnoughMoneyException
     */
    public function subtract(Account $account, Money $money): void
    {
        if ($money->greaterThan($account->getAmount())) {
            throw new NotEnoughMoneyException('Account has not enough money');
        }
        $account->setAmount($account->getAmount()->subtract($money));
    }

    /**
     * @param Account $fromAccount
     * @param Account $toAccount
     * @param Money $money
     * @throws NotEnoughMoneyException
     */
    public function transfer(Account $fromAccount, Account $toAccount, Money $money): void
    {
        if ($money->greaterThan($fromAccount->getAmount())) {
            throw new NotEnoughMoneyException('Account has not enough money');
        }
        $fromAccount->setAmount($fromAccount->getAmount()->subtract($money));
        $toAccount->setAmount($toAccount->getAmount()->add($money));
    }
}