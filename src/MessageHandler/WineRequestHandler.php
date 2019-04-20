<?php

namespace App\MessageHandler;

use App\Message\WineRequest;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class WineRequestHandler implements MessageHandlerInterface
{
    public function __invoke(WineRequest $message)
    {
        echo 'Message: ' . $message->getWineId() . ' was received';
    }
}