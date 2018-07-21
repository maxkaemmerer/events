<?php

declare(strict_types=1);

namespace MaxKaemmerer\Events\Exception;


class PayloadItemNotFound extends EventException
{
    /**
     * @param string $key
     * @return PayloadItemNotFound
     */
    public const MESSAGE = 'No item with key "%s" found in EventPayload.';

    public static function fromKey(string $key): PayloadItemNotFound
    {
        return new self(
            sprintf(
                self::MESSAGE, $key
            ), 500
        );
    }
}