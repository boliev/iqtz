<?php
namespace TZ\AccountBalance;

use PhpAmqpLib\Message\AMQPMessage;
use TZ\Exception\BrokenMQMessageException;
use TZ\Factory\MoneyTypeFactory;
use TZ\Persister\AccountPersister;
use TZ\Que\SuccessMessagePublisher;
use TZ\Repository\AccountRepository;

abstract class AccountBalanceAbstract implements AccountBalanceInterface
{
    /**
     * @var AMQPMessage
     */
    protected $message;
    /**
     * @var AccountBalanceBlocker
     */
    protected $accountBalanceBlocker;

    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @var AccountBalanceOperations
     */
    protected $accountBalanceOperations;

    /**
     * @var MoneyTypeFactory
     */
    protected $moneyTypeFactory;

    /**
     * @var AccountPersister
     */
    protected $accountPersister;

    /**
     * @var SuccessMessagePublisher
     */
    protected $successMessagePublisher;

    public function __construct(
        AMQPMessage $message,
        AccountBalanceBlocker $accountBalanceBlocker,
        AccountRepository $accountRepository,
        AccountBalanceOperations $accountBalanceOperations,
        MoneyTypeFactory $moneyTypeFactory,
        AccountPersister $accountPersister,
        SuccessMessagePublisher $successMessagePublisher
    )
    {
        $this->message = $message;
        $this->accountBalanceBlocker = $accountBalanceBlocker;
        $this->accountRepository = $accountRepository;
        $this->accountBalanceOperations = $accountBalanceOperations;
        $this->moneyTypeFactory = $moneyTypeFactory;
        $this->accountPersister = $accountPersister;
        $this->successMessagePublisher = $successMessagePublisher;
    }

    protected function setDelivered()
    {
        $this->message->delivery_info['channel']->basic_ack($this->message->delivery_info['delivery_tag']);
    }

    /**
     * @return array
     * @throws BrokenMQMessageException
     */
    protected function getTask(): array
    {
        try {
            return json_decode($this->message->getBody(), true);
        } catch (\Exception $e) {
            throw new BrokenMQMessageException('The message is broken');
        }
    }
}