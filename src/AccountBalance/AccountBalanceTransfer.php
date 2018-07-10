<?php
namespace TZ\AccountBalance;

use TZ\DAO\Account;

class AccountBalanceTransfer extends AccountBalanceAbstract
{
    /**
     * @return bool
     * @throws \TZ\Exception\AccountNotFoundException
     * @throws \TZ\Exception\BrokenMQMessageException
     * @throws \TZ\Exception\NotEnoughMoneyException
     */
    public function change(): bool
    {
        $task = $this->getTask();
        if($this->accountBalanceBlocker->isBlocked($task['fromUserId']) ||
            $this->accountBalanceBlocker->isBlocked($task['toUserId'])) {
            return false;
        }

        $this->accountBalanceBlocker->block($task['fromUserId']);
        $this->accountBalanceBlocker->block($task['toUserId']);
        $fromAccount = $this->accountRepository->getAccount($task['fromUserId']);
        $toAccount = $this->accountRepository->getAccount($task['toUserId']);
        $this->transfer($fromAccount, $toAccount, $task['amount']);
        $this->accountBalanceBlocker->unblock($task['fromUserId']);
        $this->accountBalanceBlocker->unblock($task['toUserId']);
        $this->setDelivered();
        $this->successMessagePublisher->publish($task['type'], $fromAccount);
        $this->successMessagePublisher->publish($task['type'], $toAccount);

        return true;
    }

    /**
     * @param Account $fromAccount
     * @param Account $toAccount
     * @param int $money
     * @throws \TZ\Exception\NotEnoughMoneyException
     */
    private function transfer(Account $fromAccount, Account $toAccount, int $money): void
    {
        $this->accountBalanceOperations->transfer($fromAccount, $toAccount, $this->moneyTypeFactory->getMoneyType($money));
        $this->accountPersister->transactionBegin();
        $this->accountPersister->persist($fromAccount);
        $this->accountPersister->persist($toAccount);
        $this->accountPersister->transactionCommit();
    }
}