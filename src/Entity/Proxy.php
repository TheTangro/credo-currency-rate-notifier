<?php

namespace App\Entity;

use App\Repository\ProxyRepository;
use Darsyn\IP\Doctrine\MultiType;
use Darsyn\IP\Version\Multi;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProxyRepository::class)]
class Proxy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'ip')]
    private ?Multi $ip = null;

    #[ORM\Column]
    private ?int $port = null;

    #[ORM\Column(length: 10)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\OneToOne(mappedBy: 'proxy', cascade: ['persist', 'remove'])]
    private ?ProxyStats $stats = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): Multi
    {
        return $this->ip;
    }

    public function setIp(Multi $ip): self
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

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     */
    public function setLocation(?string $location): void
    {
        $this->location = $location;
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
