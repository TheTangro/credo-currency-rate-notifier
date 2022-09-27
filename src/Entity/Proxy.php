<?php

namespace App\Entity;

use App\Repository\ProxyRepository;
use Darsyn\IP\Doctrine\MultiType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProxyRepository::class)]
class Proxy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'ip')]
    private ?MultiType $ip = null;

    #[ORM\Column]
    private ?int $port = null;

    #[ORM\Column(length: 10)]
    private ?string $type = null;

    #[ORM\OneToOne(mappedBy: 'proxy', cascade: ['persist', 'remove'])]
    private ?ProxyStats $stats = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): MultiType
    {
        return $this->ip;
    }

    public function setIp($ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStats(): ?ProxyStats
    {
        return $this->stats;
    }

    public function setStats(ProxyStats $stats): self
    {
        // set the owning side of the relation if necessary
        if ($stats->getProxy() !== $this) {
            $stats->setProxy($this);
        }

        $this->stats = $stats;

        return $this;
    }
}
