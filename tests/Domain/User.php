<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\Domain;

use Fusonic\DDDExtensions\Domain\Model\AggregateRoot;
use Fusonic\DDDExtensions\Domain\Model\Traits\IntegerIdTrait;
use Fusonic\DDDExtensions\Tests\Domain\Event\RegisterUserEvent;

final class User extends AggregateRoot
{
    use IntegerIdTrait;

    private string $name;
    private ?Job $job;

    public function __construct(string $name, ?string $jobName = null)
    {
        $this->name = $name;
        $this->job = null !== $jobName ? new Job($jobName) : null;
    }

    public function getId(): UserId
    {
        return new UserId($this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getJobName(): ?string
    {
        return $this->job?->getName();
    }

    public function register(): void
    {
        $this->raise(new RegisterUserEvent($this->getId()));
    }
}
