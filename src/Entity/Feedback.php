<?php

namespace App\Entity;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\IdInterface;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Traits\ChangeDataDayTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\StatusTrait;
use App\Repository\FeedbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
class Feedback implements
    IdInterface,
    ChangeDataDayInterface
{
    use
        IdTrait,
        ChangeDataDayTrait,
        StatusTrait
        ;

    public const STATUS_NEW = 1;
    public const STATUS_VIEWED = 2;
    public const STATUS_DELETED = 3;

    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_VIEWED => 'Viewed',
        self::STATUS_DELETED => 'Deleted',
    ];

    #[ORM\Column(length: 255)]
    private ?string $fromEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $lang = null;

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): static
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): static
    {
        $this->lang = $lang;

        return $this;
    }
}
