<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Http;

final class Response
{
    /**
     * @param array<string, array<int, string>> $headers
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly string $body,
        private readonly array $headers = [],
    ) {
    }

    public function status(): int
    {
        return $this->statusCode;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function json(): ?array
    {
        if ($this->body === '') {
            return null;
        }

        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function successful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}
