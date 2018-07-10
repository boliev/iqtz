<?php
namespace TZ\Persister;

use TZ\DAO\Account;

class AccountPersister extends Persister
{
    public function persist(Account $account): void
    {
        $sql = "UPDATE accounts SET amount = :amount WHERE user_id = :user_id";
        $res = $this->database->prepare($sql);
        $res->execute(['amount'=>$account->getAmount()->getAmount(), 'user_id' => $account->getUserId()]);
    }
}