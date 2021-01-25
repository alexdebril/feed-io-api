<?php

declare(strict_types=1);

namespace App\Storage\Entity\Feed;

class Status
{
    const PENDING = 'PENDING';

    const REJECTED = 'REJECTED';

    const ACCEPTED = 'ACCEPTED';

    private string $value;

    public function __construct(string $value)
    {
        $this->set($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function is(string $value): bool
    {
        return $value === $this->getValue();
    }

    public function isAccepted(): bool
    {
        return $this->is(self::ACCEPTED);
    }

    public function isPending(): bool
    {
        return $this->is(self::PENDING);
    }

    public function isRejected(): bool
    {
        return $this->is(self::REJECTED);
    }

    private function set(string $value): void
    {
        $values = (new \ReflectionClass(self::class))->getConstants();
        if (!in_array($value, $values)) {
            throw new \UnexpectedValueException("{$value} is not a valid status");
        }
        $this->value = $value;
    }
}
