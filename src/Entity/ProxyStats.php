<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

#[ORM\Entity]
#[Index(columns: ['usage_counter', 'errors_counter'], name: "proxy_stats_index")]
class ProxyStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'stats', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private ?Proxy $proxy = null;

    #[ORM\Column(nullable: false)]
    private ?int $usageCounter = 0;

    #[ORM\Column(nullable: false)]
    private ?int $errorsCounter = 0;

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
