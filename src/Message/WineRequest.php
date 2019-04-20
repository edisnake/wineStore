<?php

namespace App\Message;

class WineRequest
{
    private $wineId;

    public function __construct($wineId)
    {
        $this->wineId = $wineId;
    }

    /**
     * @return mixed
     */
    public function getWineId()
    {
        return $this->wineId;
    }
}