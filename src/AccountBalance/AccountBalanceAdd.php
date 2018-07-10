<?php
namespace TZ\AccountBalance;

class AccountBalanceAdd extends AccountBalanceAbstract
{
    /**
     * @return bool
     * @throws \TZ\Exception\AccountNotFoundException
     * @throws \TZ\Exception\BrokenMQMessageException
     */
    public function change(): bool
    {
        $task = $this->getTask();
        if ($this->accountBalanceBlocker->isBlocked($task['userId'])) {
            return false;
        }

        $this->accountBalanceBlocker->block($task['userId']);
        $account = $this->accountRepository->getAccount($task['userId']);
        $this->accountBalanceOperations->add($account, $this->moneyTypeFactory->getMoneyType($task['amount']));
        $this->accountPersister->persist($account);
        $this->accountBalanceBlocker->unblock($task['userId']);
        $this->setDelivered();
        $this->successMessagePublisher->publish($task['type'], $account);

        return true;
    }
}
