<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events;


interface EventCourier
{
    public function dispatch(Event $event): void;

    public function subscribe(EventSubscriber $subscriber): void;
}