<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Support;

use MustafaTaj\Tabby\Contracts\HttpClientInterface;
use MustafaTaj\Tabby\Exceptions\ExceptionFactory;
use MustafaTaj\Tabby\Http\Response;

final class MockHttpClient implements HttpClientInterface
{
    /** @var list<array{method: string, path: string, payload: array<string, mixed>, query: array<string, scalar|null>, headers: array<string, string>}> */
    public array $requests = [];

    /** @var list<Response|callable|string> */
    private array $responses = [];

    private int $responseIndex = 0;

    public function pushResponse(Response|callable $response): self
    {
        $this->responses[] = $response;

        return $this;
    }

    public function pushJsonResponse(int $status, array $body = []): self
    {
        return $this->pushResponse(new Response(
            statusCode: $status,
            body: json_encode($body, JSON_THROW_ON_ERROR),
        ));
    }

    public function get(string $path, array $query = [], array $headers = []): Response
    {
        return $this->handleRequest('GET', $path, [], $query, $headers);
    }

    public function post(string $path, array $payload = [], array $headers = []): Response
    {
        return $this->handleRequest('POST', $path, $payload, [], $headers);
    }

    public function put(string $path, array $payload = [], array $headers = []): Response
    {
        return $this->handleRequest('PUT', $path, $payload, [], $headers);
    }

    public function delete(string $path, array $headers = []): Response
    {
        return $this->handleRequest('DELETE', $path, [], [], $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, scalar|null> $query
     * @param array<string, string> $headers
     */
    private function handleRequest(
        string $method,
        string $path,
        array $payload,
        array $query,
        array $headers,
    ): Response {
        $this->requests[] = [
            'method' => $method,
            'path' => $path,
            'payload' => $payload,
            'query' => $query,
            'headers' => $headers,
        ];

        $response = $this->responses[$this->responseIndex] ?? new Response(200, '{}');
        $this->responseIndex++;

        if (is_callable($response)) {
            $response = $response($method, $path, $payload, $query, $headers);
        }

        if (! $response->successful()) {
            throw ExceptionFactory::fromResponse(
                response: $response,
                method: $method,
                path: $path,
                headers: $headers,
                payload: $payload,
            );
        }

        return $response;
    }

    public function lastRequest(): ?array
    {
        if ($this->requests === []) {
            return null;
        }

        return $this->requests[array_key_last($this->requests)];
    }
}
