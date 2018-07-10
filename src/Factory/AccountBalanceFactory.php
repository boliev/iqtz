<?php
namespace TZ\Factory;

use PhpAmqpLib\Message\AMQPMessage;
use TZ\AccountBalance\AccountBalanceAdd;
use TZ\AccountBalance\AccountBalanceBlocker;
use TZ\AccountBalance\AccountBalanceInterface;
use TZ\AccountBalance\AccountBalanceOperations;
use TZ\AccountBalance\AccountBalanceSubtract;
use TZ\AccountBalance\AccountBalanceTransfer;
use TZ\Exception\BrokenMQMessageException;
use TZ\Exception\CantProcessTaskException;
use TZ\Persister\AccountPersister;
use TZ\Que\SuccessMessagePublisher;
use TZ\Repository\AccountRepository;

class AccountBalanceFactory
{
    /**
     * @param AMQPMessage $message
     * @return AccountBalanceInterface
     * @throws BrokenMQMessageException
     * @throws CantProcessTaskException
     */
    public function getAccountBalance(AMQPMessage $message): AccountBalanceInterface
    {
        try {
            $task = json_decode($message->getBody(), true);
        } catch (\Exception $e) {
            throw new BrokenMQMessageException();
        }

        $databaseFactory = new DatabaseFactory();
        $database = $databaseFactory->getDatabase();
        $redisFactory = new RedisFactory();
        $redis = $redisFactory->getRedis();
        $balanceOperations = new AccountBalanceOperations();
        $moneyTypeFactory = new MoneyTypeFactory();
        $accountRepository = new AccountRepository($database, $moneyTypeFactory);
        $accountPersister = new AccountPersister($database);
        $accountBlocker = new AccountBalanceBlocker($redis);
        $rabbitFactory = new RabbitChannelFactory();
        $successChannel = $rabbitFactory->getChannel();
        $successPublisher = new SuccessMessagePublisher($successChannel);

        if(isset($task['type']) && isset($task['userId']) && isset($task['amount']) && $task['type'] === 'add') {
            return new AccountBalanceAdd(
                $message,
                $accountBlocker,
                $accountRepository,
                $balanceOperations,
                $moneyTypeFactory,
                $accountPersister,
                $successPublisher
            );
        } elseif(isset($task['type']) && isset($task['amount']) && $task['type'] === 'subtract') {
            return new AccountBalanceSubtract(
                $message,
                $accountBlocker,
                $accountRepository,
                $balanceOperations,
                $moneyTypeFactory,
                $accountPersister,
                $successPublisher
            );
        } elseif(isset($task['type']) && isset($task['amount']) && $task['type'] === 'transfer' && isset($task['fromUserId']) && isset($task['toUserId'])) {
            return new AccountBalanceTransfer(
                $message,
                $accountBlocker,
                $accountRepository,
                $balanceOperations,
                $moneyTypeFactory,
                $accountPersister,
                $successPublisher
            );
        }
        throw new CantProcessTaskException('Can\'t process task');
    }
}