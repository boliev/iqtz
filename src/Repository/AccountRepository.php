<?php
namespace TZ\Repository;

use TZ\DAO\Account;
use TZ\Exception\AccountNotFoundException;
use TZ\Factory\MoneyTypeFactory;

class AccountRepository
{
    /**
     * @var \PDO
     */
    private $database;

    /**
     * @var MoneyTypeFactory
     */
    private $moneyTypeFactory;

    public function __construct(\PDO $database, MoneyTypeFactory $moneyTypeFactory)
    {
        $this->database = $database;
        $this->moneyTypeFactory = $moneyTypeFactory;
    }

    /**
     * @param int $id
     * @return Account
     * @throws AccountNotFoundException
     */
    public function getAccount(int $id)
    {
        $res = $this->database->prepare('SELECT user_id, amount FROM accounts WHERE user_id = :userId');
        $res->execute([':userId' => $id]);
        $data =  $res->fetch();
        if (!$data) {
            throw new AccountNotFoundException('No such account');
        }

        return new Account($data['user_id'], $this->moneyTypeFactory->getMoneyType($data['amount']));
    }
}
