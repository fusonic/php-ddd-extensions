<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

use Fusonic\DDDExtensions\Domain\Event\DomainEventInterface;

/**
 * Base class for aggregates in the domain.
 */
abstract class AggregateRoot implements EntityInterface
{
    /**
     * @var DomainEventInterface[]
     */
    private array $events = [];

    abstract public function getId(): mixed;

    /**
     * @return DomainEventInterface[]
     */
    public function popEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    protected function raise(DomainEventInterface $event): void
    {
        $this->events[] = $event;
    }
}
