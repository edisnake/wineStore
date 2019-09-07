<?php

namespace App\Message;


class WineOrderProcess
{
    /**
     * @var int
     */
    private $wineOrderHead;

    /**
     * @var string
     */
    private $processDate;

    /**
     * WineOrderProcess constructor.
     * Queuing the wine order
     *
     * @param int $wineOrderHead
     * @param string $processDate
     */
    public function __construct(int $wineOrderHead, string $processDate)
    {
        $this->wineOrderHead = $wineOrderHead;
        $this->processDate = $processDate;
    }

    public function getWineOrderHead(): int
    {
        return $this->wineOrderHead;
    }

    public function getProcessDate(): string
    {
        return $this->processDate;
    }
}
