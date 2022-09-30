<?php

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[Index(columns: ['exchange_date', 'exchange_type'], name: "exchange_date_index")]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $currencyCode = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 44, scale: 36)]
    private ?string $buyRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 44, scale: 36)]
    private ?string $sellRate = null;

    #[ORM\Column(name: 'exchange_type', length: 36)]
    private ?string $exchangeType = null;

    #[ORM\Column(name: 'exchange_date', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $exchangeDate = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    /**
     * @param string|null $currencyCode
     */
    public function setCurrencyCode(?string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return string|null
     */
    public function getBuyRate(): ?string
    {
        return $this->buyRate;
    }

    /**
     * @param string|null $buyRate
     */
    public function setBuyRate(?string $buyRate): void
    {
        $this->buyRate = $buyRate;
    }

    /**
     * @return string|null
     */
    public function getSellRate(): ?string
    {
        return $this->sellRate;
    }

    /**
     * @param string|null $sellRate
     */
    public function setSellRate(?string $sellRate): void
    {
        $this->sellRate = $sellRate;
    }

    /**
     * @return string|null
     */
    public function getExchangeType(): ?string
    {
        return $this->exchangeType;
    }

    /**
     * @param string $exchangeType
     * @return $this
     */
    public function setExchangeType(string $exchangeType): self
    {
        $this->exchangeType = $exchangeType;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getExchangeDate(): ?\DateTimeImmutable
    {
        return $this->exchangeDate;
    }

    /**
     * @param \DateTimeImmutable $exchangeDate
     * @return $this
     */
    public function setExchangeDate(\DateTimeImmutable $exchangeDate): self
    {
        $this->exchangeDate = $exchangeDate;

        return $this;
    }
}
