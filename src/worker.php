<?php
namespace TZ;
require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use TZ\Factory\AccountBalanceFactory;
use TZ\Factory\ErrorMessagePublisherFactory;
use TZ\Factory\RabbitChannelFactory;

$rabbitFactory = new RabbitChannelFactory();
$channel = $rabbitFactory->getChannel();
$channel->exchange_declare(getenv('RABBIT_EXCHANGE'), 'direct', false, false, false);

$callback = function (AMQPMessage $msg) {
    try {
        $accountBalanceFactory = new AccountBalanceFactory();
        $accountBalance = $accountBalanceFactory->getAccountBalance($msg);
        $accountBalance->change();
    } catch(\Exception $e) {
        $errorMessagePublisherFactory = new ErrorMessagePublisherFactory();
        $errorMessagePublisher = $errorMessagePublisherFactory->getPublisher();
        $errorMessagePublisher->publish($e, $msg->getBody());
    }
};

$queue_name = 'user.balance';
$channel->queue_declare($queue_name, false, false, false, false);
$channel->queue_bind($queue_name, getenv('RABBIT_EXCHANGE'), 'user.balance.add');
$channel->queue_bind($queue_name, getenv('RABBIT_EXCHANGE'), 'user.balance.subtract');
$channel->queue_bind($queue_name, getenv('RABBIT_EXCHANGE'), 'user.balance.transfer');
$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$connection = $channel->getConnection();
$channel->close();
$connection->close();