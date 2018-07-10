<?php

namespace TZ\Que;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use TZ\DAO\Account;

class SuccessMessagePublisher
{
    /**
     * @var \AMQPChannel
     */
    private $channel;

    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    public function publish(string $type, Account $account)
    {
        $successMsg = json_encode(['userId' => $account->getUserId()]);
        $this->channel->exchange_declare(getenv('RABBIT_EXCHANGE'), 'direct', false, false, false);
        $this->channel->basic_publish(new AMQPMessage($successMsg), getenv('RABBIT_EXCHANGE'), sprintf('user.balance.%s.success', $type));
    }
}