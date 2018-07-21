<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events\Implementations;


use MaxKaemmerer\Events\EventSubscription;

final class Subscription implements EventSubscription
{

    /** @var string */
    private $eventName;

    /** @var int */
    private $priority;

    private function __construct()
    {
    }

    public static function fromEventNameAndPriority(string $eventName, int $priority): EventSubscription
    {
        $instance = new self();

        $instance->eventName = $eventName;
        $instance->priority = $priority;

        return $instance;
    }

    public function event(): string
    {
        return $this->eventName;
    }

    public function priority(): int
    {
        return $this->priority;
    }
}