<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events;


interface EventSubscriber
{

    /**
     * @param Event $event
     */
    public function on(Event $event): void;

    /**
     * @return EventSubscription
     */
    public function subscription(): EventSubscription;

}