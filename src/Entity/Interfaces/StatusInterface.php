<?php

namespace App\Entity\Interfaces;


interface StatusInterface
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_EDIT = 2;
    public const STATUS_DELETED = 3;

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_EDIT => 'Edit',
        self::STATUS_DELETED => 'Deleted',
    ];

    public function getStatus(): ?int;

    public function setStatus(?int $status): static;
}