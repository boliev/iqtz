<?php
namespace TZ\Factory;

use TZ\Que\ErrorMessagePublisher;

class ErrorMessagePublisherFactory
{
    public function getPublisher()
    {
        $rabbitFactory = new RabbitChannelFactory();
        $errorChannel = $rabbitFactory->getChannel();
        return new ErrorMessagePublisher($errorChannel);
    }
}