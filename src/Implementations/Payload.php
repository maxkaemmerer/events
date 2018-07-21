<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events\Implementations;


use MaxKaemmerer\Events\EventPayload;
use MaxKaemmerer\Events\Exception\PayloadItemNotFound;

final class Payload implements EventPayload
{

    private $payload = [];

    private function __construct()
    {
    }


    public static function fromArray(array $payload): EventPayload
    {
        $instance = new self();
        $instance->payload = $payload;
        return $instance;
    }

    /**
     * @param string $key
     * @return mixed
     * Fetches an item from the payload by its key. Throws a PayloadItemNotFound Exception if no item with this key is found.
     * @throws PayloadItemNotFound
     */
    public function get(string $key)
    {
        if(!array_key_exists($key, $this->payload)){
            throw PayloadItemNotFound::fromKey($key);
        }

        return $this->payload[$key];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}