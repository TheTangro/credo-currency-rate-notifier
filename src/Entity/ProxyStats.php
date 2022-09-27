<?php

namespace App\Entity;

use App\Repository\ProxyStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProxyStatsRepository::class)]
class ProxyStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'stats', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Proxy $proxy = null;

    #[ORM\Column]
    private ?int $usageCounter = null;

    #[ORM\Column]
    private ?int $errorsCounter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProxy(): ?Proxy
    {
        return $this->proxy;
    }

    public function setProxy(Proxy $proxy): self
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function getUsageCounter(): ?int
    {
        return $this->usageCounter;
    }

    public function setUsageCounter(int $usageCounter): self
    {
        $this->usageCounter = $usageCounter;

        return $this;
    }

    public function getErrorsCounter(): ?int
    {
        return $this->errorsCounter;
    }

    public function setErrorsCounter(int $errorsCounter): self
    {
        $this->errorsCounter = $errorsCounter;

        return $this;
    }
}
