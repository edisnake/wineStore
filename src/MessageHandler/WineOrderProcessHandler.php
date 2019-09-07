<?php

namespace App\MessageHandler;

use App\Message\WineOrderProcess;
use App\Service\WineOrderService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class WineOrderProcessHandler implements MessageHandlerInterface
{
    /**
     * @var WineOrderService
     */
    private $wineOrderService;

    /**
     * WineOrderProcessHandler constructor.
     * @param WineOrderService $wineOrderService
     */
    public function __construct(WineOrderService $wineOrderService)
    {
        $this->wineOrderService = $wineOrderService;
    }

    /**
     * Executed when the queue is processed
     *
     * @param WineOrderProcess $message
     */
    public function __invoke(WineOrderProcess $message)
    {
        echo " -- Processing wine order: " . $message->getWineOrderHead() . ' order date: ' . $message->getProcessDate();
        $this->wineOrderService->processOrder($message->getWineOrderHead(), $message->getProcessDate());
    }
}