<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WineOrderHeadRepository")
 */
class WineOrderHead
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedAt;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Waiter")
     * @ORM\JoinColumn(nullable=false)
     */
    private $waiter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sommelier")
     */
    private $sommelier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?\DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

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

    public function getWaiter(): ?Waiter
    {
        return $this->waiter;
    }

    public function setWaiter(?Waiter $waiter): self
    {
        $this->waiter = $waiter;

        return $this;
    }

    public function getSommelier(): ?Sommelier
    {
        return $this->sommelier;
    }

    public function setSommelier(?Sommelier $sommelier): self
    {
        $this->sommelier = $sommelier;

        return $this;
    }
}
