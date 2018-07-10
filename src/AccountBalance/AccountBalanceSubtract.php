<?php
namespace TZ\AccountBalance;

class AccountBalanceSubtract extends AccountBalanceAbstract
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
        if($this->accountBalanceBlocker->isBlocked($task['userId'])) {
            return false;
        }

        $this->accountBalanceBlocker->block($task['userId']);
        $account = $this->accountRepository->getAccount($task['userId']);
        $this->accountBalanceOperations->subtract($account, $this->moneyTypeFactory->getMoneyType($task['amount']));
        $this->accountPersister->persist($account);
        $this->accountBalanceBlocker->unblock($task['userId']);
        $this->setDelivered();
        $this->successMessagePublisher->publish($task['type'], $account);

        return true;
    }
}