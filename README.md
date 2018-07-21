# maxkaemmerer/events
[![Travis branch](https://img.shields.io/travis/maxkaemmerer/events/master.svg?style=flat-square)](https://travis-ci.org/maxkaemmerer/events)
[![Coveralls github](https://img.shields.io/coveralls/maxkaemmerer/events/master.svg?style=flat-square&branch=master)](https://coveralls.io/github/maxkaemmerer/events?branch=master)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/maxkaemmerer/events.svg?style=flat-square)](https://packagist.org/packages/maxkaemmerer/events)
[![Packagist](https://img.shields.io/packagist/v/maxkaemmerer/events.svg?style=flat-square)](https://packagist.org/packages/maxkaemmerer/events)
[![Packagist](https://img.shields.io/packagist/l/maxkaemmerer/events.svg?style=flat-square)](https://packagist.org/packages/maxkaemmerer/events)

## Description:

This library offers interfaces and implementations for a simple event, event-subscriber, event-courier structure.

This is of course not an original idea, but my preferred and fairly simple implementation.

The code is fully tested, I however do not take responsibility for use in production. 

Use at your own risk.

## Installation:

``composer require maxkaemmerer/events``

## Usage:
Generally you don't want to subscribe each ``EventSubscriber`` by hand. You might want to use dependency injection via a container or service-manager.

(An example for the Symfony Framework would be using a ``CompilerPass``)

You would also want to inject the ``EventCourier`` itself via dependency injection wherever you need it.

Feel free to create your own implementations of ``EventCourier``, ``Subscription`` and ``Payload`` if you require something more advanced.


#### Subscribe an EventSubscriber:
Subscribe an ``EventSubscriber`` to the ``EventCourier``. The ``EventSubscriber``'s ``subscription()`` method returns a ``Subscription``, which contains the name of the ``Event`` that the ``EventSubscriber`` subscribes to and the priority at which it wants to be notified.

(The higher the priority, the earlier the ``EventSubscriber`` get notified of the ``Event``, assuming there are other ``EventSubscriber``s with lower priority.)

Best practice would be using the fully qualified class name of the event. ``MyEvent::class``

The ``EventSubscriber::on($event)`` method is where your actual domain logic happens.

Feel free to inject services, a container, or whatever else you need, into your ``EventSubscriber``s.


    $courier = new SimpleEventCourier();
    
    
    $courier->subscribe(new class implements EventSubscriber
    {
    
        /**
         * @param Event $event
         */
        public function on(Event $event): void
        {
            echo 'Notify Shipping!'  . PHP_EOL;
        }
    
        /**
         * @return EventSubscription
         */
        public function subscription(): EventSubscription
        {
            return Subscription::fromEventNameAndPriority('PaymentReceived', 50);
        }
    });
    
#### Dispatch an Event:
Dispatching an ``Event`` causes the ``EventCourier`` to notify all ``EventSubscriber``s, who's ``Subscription::event()`` method matches the ``Event``'s name, specified by ``Event:name()``, and calls their ``EventSubscriber::on($event)`` method in order of priority.

IMPORTANT: ``EventSubscriber``'s and the ``EventCourier`` never return anything.

    ...
    
    // The EventSubscriber was subscribed
    
    
    echo 'Payment received.'  . PHP_EOL;
    $courier->dispatch(new class implements Event
    {
        public function payload(): EventPayload
        {
            // the payload should of course be built or set in the constructor
            return Payload::fromArray(['price' => '99.99€', 'method' => 'creditCard', 'timestamp' => '12345678']);
        }
    
        public function name(): string
        {
            // best practice would be using the fully qualified class name MyEvent::class
            return 'PaymentReceived';
        }
    });
    
Result:

    Payment received.
    Notify Shipping!

#### Full Example:


    <?php
    
    require_once __DIR__ . '/vendor/autoload.php';
    
    use MaxKaemmerer\Events\Event;
    use MaxKaemmerer\Events\EventPayload;
    use MaxKaemmerer\Events\EventSubscriber;
    use MaxKaemmerer\Events\EventSubscription;
    use MaxKaemmerer\Events\Implementations\Payload;
    use MaxKaemmerer\Events\Implementations\SimpleEventCourier;
    use MaxKaemmerer\Events\Implementations\Subscription;
    
    $courier = new SimpleEventCourier();
    
    
    $courier->subscribe(new class implements EventSubscriber
    {
    
        /**
         * @param Event $event
         */
        public function on(Event $event): void
        {
            echo 'Notify Shipping!' . PHP_EOL;
        }
    
        /**
         * @return EventSubscription
         */
        public function subscription(): EventSubscription
        {
            return Subscription::fromEventNameAndPriority('PaymentReceived', 50);
        }
    });
    
    
    $courier->subscribe(new class implements EventSubscriber
    {
        /**
         * @param Event $event
         */
        public function on(Event $event): void
        {
            echo 'Send Receipt! Payed: ' . $event->payload()->get('price') . PHP_EOL;
        }
    
        /**
         * @return EventSubscription
         */
        public function subscription(): EventSubscription
        {
            return Subscription::fromEventNameAndPriority('PaymentReceived', 60);
        }
    });
    
    
    echo 'Payment received.' . PHP_EOL;
    $courier->dispatch(new class implements Event
    {
        public function payload(): EventPayload
        {
            // the payload should of course be built or set in the constructor
            return Payload::fromArray(['price' => '99.99€', 'method' => 'creditCard', 'timestamp' => '12345678']);
        }
    
        public function name(): string
        {
            // best practice would be using the fully qualified class name MyEvent::class
            return 'PaymentReceived';
        }
    });
    
Result:

    Payment received.
    Send Receipt!
    Notify Shipping!

``Send Receipt!`` gets echoed first since the corresponding ``EventSubscriber``'s ``Subscription::priority()`` is higher.