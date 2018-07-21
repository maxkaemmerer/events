<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events\Tests\Unit\Implementations;


use MaxKaemmerer\Events\Event;
use MaxKaemmerer\Events\EventCourier;
use MaxKaemmerer\Events\EventSubscriber;
use MaxKaemmerer\Events\EventSubscription;
use MaxKaemmerer\Events\Implementations\SimpleEventCourier;
use MaxKaemmerer\Events\Implementations\Subscription;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class SimpleEventCourierTest extends TestCase
{

    private const EVENT_NAME_A = 'eventNameA';

    private const EVENT_NAME_B = 'eventNameB';

    private const LOW_PRIORITY = 13;

    private const HIGH_PRIORITY = 99;

    private const MEDIUM_PRIORITY = 45;

    /** @var EventCourier */
    private $courier;

    /** @var EventSubscriber|ObjectProphecy */
    private $eventSubscriber;

    /** @var EventSubscriber|ObjectProphecy */
    private $eventSubscriberTwo;

    /** @var Event|ObjectProphecy */
    private $event;

    public $lastSubscriberCalled = null;

    public $firstSubscriberCalled = null;

    public function setUp()
    {
        $this->courier = new SimpleEventCourier();
    }


    /**
     * @test
     **/
    public function should_implement_EventCourier(): void
    {
        self::assertInstanceOf(EventCourier::class, $this->courier);
    }

    /**
     * @test
     **/
    public function should_subscribe_and_call_EventSubscriber(): void
    {
        $this->givenAMatchingEventSubscriberIsSubscribed();

        $this->thenTheMatchingEventSubscriberShouldBeNotified();

        $this->whenTheEventIsDispatched();
    }

    /**
     * @test
     **/
    public function should_call_subscribers_by_priority(): void
    {
        $this->givenThereAreThreeSubscribersWhosePriorityIsUnsorted();

        $this->whenACorrespondingEventIsDispatched();

        $this->thenTheEventSubscribersShouldBeCalledInOrderOfPriority();
    }

    /**
    * @test
    **/
    public function should_only_call_EventSubscribers_subscribing_to_the_dispatched_event(): void
    {
        $this->givenAMatchingEventSubscriberIsSubscribed();
        $this->givenANonMatchingEventSubscriberIsSubscribed();

        $this->thenTheMatchingEventSubscriberShouldBeNotified();
        $this->thenTheNonMatchingEventSubscriberShouldNotBeNotified();

        $this->whenTheEventIsDispatched();
    }

    /**
     * @taest
     **/
    public function should_only_sort_subscribers_when_new_subscribers_were_added(): void
    {

    }

    private function givenThereAreThreeSubscribersWhosePriorityIsUnsorted(): void
    {
        $lowPrioritySubscription = Subscription::fromEventNameAndPriority(self::EVENT_NAME_A, self::LOW_PRIORITY);
        $highPrioritySubscription = Subscription::fromEventNameAndPriority(self::EVENT_NAME_A, self::HIGH_PRIORITY);
        $mediumPrioritySubscription = Subscription::fromEventNameAndPriority(self::EVENT_NAME_A, self::MEDIUM_PRIORITY);

        $lowPriorityEventSubscriber = new TestEventSubscriber($lowPrioritySubscription, $this);
        $highPriorityEventSubscriber = new TestEventSubscriber($highPrioritySubscription, $this);
        $mediumPriorityEventSubscriber = new TestEventSubscriber($mediumPrioritySubscription, $this);

        $this->courier->subscribe($lowPriorityEventSubscriber);
        $this->courier->subscribe($highPriorityEventSubscriber);
        $this->courier->subscribe($mediumPriorityEventSubscriber);
    }

    private function whenACorrespondingEventIsDispatched(): void
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->name()->willReturn(self::EVENT_NAME_A);

        $this->whenTheEventIsDispatched();
    }

    private function thenTheEventSubscribersShouldBeCalledInOrderOfPriority(): void
    {
        self::assertEquals(self::LOW_PRIORITY, $this->lastSubscriberCalled);
        self::assertEquals(self::HIGH_PRIORITY, $this->firstSubscriberCalled);
    }

    private function givenAMatchingEventSubscriberIsSubscribed(): void
    {
        $subscription = Subscription::fromEventNameAndPriority(self::EVENT_NAME_A, self::LOW_PRIORITY);

        $this->eventSubscriber = $this->prophesize(EventSubscriber::class);
        $this->eventSubscriber->subscription()->willReturn($subscription);
        $this->courier->subscribe($this->eventSubscriber->reveal());
    }

    private function givenANonMatchingEventSubscriberIsSubscribed(): void
    {
        $subscriptionTwo = Subscription::fromEventNameAndPriority(self::EVENT_NAME_B, self::LOW_PRIORITY);
        $this->eventSubscriberTwo = $this->prophesize(EventSubscriber::class);
        $this->eventSubscriberTwo->subscription()->willReturn($subscriptionTwo);
        $this->courier->subscribe($this->eventSubscriberTwo->reveal());
    }

    private function thenTheMatchingEventSubscriberShouldBeNotified(): void
    {
        $this->event = $this->prophesize(Event::class);
        $this->event->name()->willReturn(self::EVENT_NAME_A);
        $this->eventSubscriber->on($this->event->reveal())->shouldBeCalled();
    }

    private function thenTheNonMatchingEventSubscriberShouldNotBeNotified(): void
    {
        $this->eventSubscriberTwo->on($this->event->reveal())->shouldNotBeCalled();
    }

    private function whenTheEventIsDispatched(): void
    {
        $this->courier->dispatch($this->event->reveal());
    }
}

class TestEventSubscriber implements EventSubscriber
{
    /**
     * @var EventSubscription
     */
    private $subscription;
    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * TestEventSubscriber constructor.
     * @param EventSubscription $subscription
     * @param SimpleEventCourierTest $testCase
     */
    public function __construct(EventSubscription $subscription, SimpleEventCourierTest $testCase)
    {
        $this->subscription = $subscription;
        $this->testCase = $testCase;
    }


    /**
     * @param Event $event
     */
    public function on(Event $event): void
    {
        if (!$this->testCase->firstSubscriberCalled) {
            $this->testCase->firstSubscriberCalled = $this->subscription->priority();
        }
        $this->testCase->lastSubscriberCalled = $this->subscription->priority();
    }

    /**
     * @return EventSubscription
     */
    public function subscription(): EventSubscription
    {
        return $this->subscription;
    }
}
