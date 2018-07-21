<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events;


interface Event
{
    public function payload(): EventPayload;

    public function name(): string;
}