<?php
namespace TZ\Factory;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitChannelFactory
{
    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel()
    {
        $connection = new AMQPStreamConnection(
            getenv('RABBIT_HOST'),
            getenv('RABBIT_PORT'),
            getenv('RABBIT_USER'),
            getenv('RABBIT_PASSWORD')
        );
        return $connection->channel();
    }
}