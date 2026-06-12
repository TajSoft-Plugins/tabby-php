<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Contracts;

use MustafaTaj\Tabby\Http\Response;

interface HttpClientInterface
{
    /**
     * @param array<string, scalar|null> $query
     * @param array<string, string> $headers
     */
    public function get(string $path, array $query = [], array $headers = []): Response;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function post(string $path, array $payload = [], array $headers = []): Response;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function put(string $path, array $payload = [], array $headers = []): Response;

    /**
     * @param array<string, string> $headers
     */
    public function delete(string $path, array $headers = []): Response;
}
