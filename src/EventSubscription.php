<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events;


interface EventSubscription
{
    public function event(): string;

    public function priority(): int;
}