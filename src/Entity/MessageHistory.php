<?php

namespace App\Entity;

use App\Repository\MessageHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

#[ORM\Entity(repositoryClass: MessageHistoryRepository::class)]
class MessageHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sender = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $sentDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getSentDate(): ?\DateTimeInterface
    {
        return $this->sentDate;
    }

    /**
     * @param \DateTimeInterface|null $sentDate
     * @return MessageHistory
     */
    public function setSentDate(?\DateTimeInterface $sentDate): MessageHistory
    {
        $this->sentDate = $sentDate;
        return $this;
    }
}
