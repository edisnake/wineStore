<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WineOrderDetailRepository")
 */
class WineOrderDetail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WineFeed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wine;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WineOrderHead")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wineOrderHead;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWine(): ?WineFeed
    {
        return $this->wine;
    }

    public function setWine(?WineFeed $wine): self
    {
        $this->wine = $wine;

        return $this;
    }

    public function getWineOrderHead(): ?WineOrderHead
    {
        return $this->wineOrderHead;
    }

    public function setWineOrderHead(?WineOrderHead $wineOrderHead): self
    {
        $this->wineOrderHead = $wineOrderHead;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
