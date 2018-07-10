<?php

namespace TZ\Que;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class ErrorMessagePublisher
{
    /**
     * @var \AMQPChannel
     */
    private $channel;

    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    public function publish(\Exception $exception, string $task)
    {
        $successMsg = json_encode([
            'task' => $task,
            'error' => $exception->getMessage()
        ]);

        $this->channel->exchange_declare(getenv('RABBIT_EXCHANGE'), 'direct', false, false, false);
        $this->channel->basic_publish(new AMQPMessage($successMsg), getenv('RABBIT_EXCHANGE'), 'user.balance.errors');
    }
}
