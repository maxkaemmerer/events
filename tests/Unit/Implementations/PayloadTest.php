<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events\Tests\Unit\Implementations;


use MaxKaemmerer\Events\EventPayload;
use MaxKaemmerer\Events\Exception\PayloadItemNotFound;
use MaxKaemmerer\Events\Implementations\Payload;
use PHPUnit\Framework\TestCase;

class PayloadTest extends TestCase
{

    private const EXISTING_VALUE = 1234;

    private const NON_EXISTENT_KEY = 'nonexistentkey';

    private const EXISTING_KEY = 'myKey';

    private const PAYLOAD_ARRAY = [self::EXISTING_KEY => self::EXISTING_VALUE];

    /** @var EventPayload */
    private $payload;

    public function setUp()
    {
        $this->payload = Payload::fromArray(self::PAYLOAD_ARRAY);
    }

    /**
     * @test
     **/
    public function should_implement_EventPayload(): void
    {
        $payload = Payload::fromArray([]);
        self::assertInstanceOf(EventPayload::class, $payload);
    }

    /**
     * @test
     **/
    public function should_return_the_same_array_that_goes_in(): void
    {
        // Given a payload is created with a certain array of items

        $this->thenThatSameArrayShouldBeReturned();
    }

    /**
     * @test
     *
     * @throws PayloadItemNotFound
     */
    public function should_correctly_get_an_item(): void
    {
        // Given there is an item in the payload

        $this->thenThatItemShouldBeRetrievableByItsKey();
    }

    /**
     * @test
     *
     * @throws PayloadItemNotFound
     */
    public function should_throw_a_PayloadItemNotFound_exception_if_the_item_does_not_exist(): void
    {
        // Given a certain an item with a specific key is not in the payload

        $this->thenAPayloadItemNotFoundExceptionShouldBeThrown();

        $this->whenARetrievalOfTheNonExistentItemIsAttempted();
    }

    private function thenThatSameArrayShouldBeReturned(): void
    {
        self::assertEquals(self::PAYLOAD_ARRAY, $this->payload->toArray());
    }

    /**
     * @throws PayloadItemNotFound
     */
    private function thenThatItemShouldBeRetrievableByItsKey(): void
    {
        self::assertEquals(self::EXISTING_VALUE, $this->payload->get(self::EXISTING_KEY));
    }

    private function thenAPayloadItemNotFoundExceptionShouldBeThrown(): void
    {
        $this->expectException(PayloadItemNotFound::class);
        $this->expectExceptionMessage(sprintf(PayloadItemNotFound::MESSAGE, self::NON_EXISTENT_KEY));
        $this->expectExceptionCode(500);
    }

    /**
     * @throws PayloadItemNotFound
     */
    private function whenARetrievalOfTheNonExistentItemIsAttempted(): void
    {
        $this->payload->get(self::NON_EXISTENT_KEY);
    }
}
