<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events\Implementations;


use MaxKaemmerer\Events\Event;
use MaxKaemmerer\Events\EventCourier;
use MaxKaemmerer\Events\EventSubscriber;

final class SimpleEventCourier implements EventCourier
{

    private $subscribers = [];

    private $isSorted = [];

    public function dispatch(Event $event): void
    {
        if ($this->subscribersExist($event)) {
            $this->sortSubscribersIfRequired($event);
            $this->notifySubscribers($event);
        }
    }

    public function subscribe(EventSubscriber $subscriber): void
    {
        $subscription = $subscriber->subscription();
        $this->subscribers[$subscription->event()][$subscription->priority()][] = $subscriber;
        $this->isSorted[$subscription->event()] = false;
    }

    /**
     * @param Event $event
     */
    private function sortSubscribersIfRequired(Event $event): void
    {
        if (!$this->isSorted[$event->name()]) {
            \krsort($this->subscribers[$event->name()], SORT_NUMERIC);
            $this->isSorted[$event->name()] = true;
        }
    }

    /**
     * @param Event $event
     * @return bool
     */
    private function subscribersExist(Event $event): bool
    {
        return \array_key_exists($event->name(), $this->subscribers);
    }

    /**
     * @param Event $event
     */
    private function notifySubscribers(Event $event): void
    {
        $priorities = $this->subscribers[$event->name()];
        foreach ($priorities as $priority) {
            /** @var EventSubscriber $subscriber */
            foreach ($priority as $subscriber) {
                $subscriber($event);
            }
        }
    }
}