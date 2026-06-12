<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Exceptions;

final class ConfigurationException extends TabbyException
{
    public static function missing(string $key): self
    {
        return new self(sprintf('Missing required Tabby configuration value: %s.', $key));
    }

    public static function invalid(string $message): self
    {
        return new self($message);
    }
}
