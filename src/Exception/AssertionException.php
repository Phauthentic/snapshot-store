<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Exception;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AssertionException extends SnapshotStoreException
{
    protected static string $missingKeyMessage = 'The array is missing the `%s` key';

    protected static string $notEmptyStringMessage = 'The passed value (%s) is not a non-empty string';

    /**
     * @param mixed $value
     * @return self
     */
    public static function notEmptyString(mixed $value): self
    {
        return new self(sprintf(
            static::$notEmptyStringMessage,
            gettype($value)
        ));
    }

    /**
     * @param string $key
     * @return self
     */
    public static function missingArrayKey(string $key): self
    {
        return new self(sprintf(
            static::$missingKeyMessage,
            $key
        ));
    }
}
