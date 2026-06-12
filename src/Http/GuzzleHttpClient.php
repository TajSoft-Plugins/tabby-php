<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use MustafaTaj\Tabby\Config\TabbyConfig;
use MustafaTaj\Tabby\Contracts\HttpClientInterface;
use MustafaTaj\Tabby\Exceptions\ExceptionFactory;
use MustafaTaj\Tabby\Exceptions\NetworkException;

final class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;

    public function __construct(
        private readonly TabbyConfig $config,
        ?Client $client = null,
    ) {
        $this->client = $client ?? new Client([
            'base_uri' => $this->config->getBaseUrl().'/',
            'timeout' => $this->config->getTimeout(),
            'connect_timeout' => $this->config->getConnectTimeout(),
            'http_errors' => false,
            'debug' => $this->config->isHttpDebugEnabled(),
        ]);
    }

    public function get(string $path, array $query = [], array $headers = []): Response
    {
        return $this->request('GET', $path, [], $query, $headers);
    }

    public function post(string $path, array $payload = [], array $headers = []): Response
    {
        return $this->request('POST', $path, $payload, [], $headers);
    }

    public function put(string $path, array $payload = [], array $headers = []): Response
    {
        return $this->request('PUT', $path, $payload, [], $headers);
    }

    public function delete(string $path, array $headers = []): Response
    {
        return $this->request('DELETE', $path, [], [], $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, scalar|null> $query
     * @param array<string, string> $headers
     */
    private function request(
        string $method,
        string $path,
        array $payload = [],
        array $query = [],
        array $headers = [],
    ): Response {
        $normalizedPath = $this->normalizePath($path);

        $options = [
            'headers' => $headers,
        ];

        if ($query !== []) {
            $options['query'] = $query;
        }

        if ($payload !== [] && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options['json'] = $payload;
        }

        try {
            $guzzleResponse = $this->client->request($method, $normalizedPath, $options);
        } catch (ConnectException $exception) {
            throw ExceptionFactory::fromNetworkError(
                message: 'Unable to connect to Tabby API.',
                method: $method,
                path: $normalizedPath,
                headers: $headers,
                payload: $payload,
                previous: $exception,
            );
        } catch (RequestException $exception) {
            if ($exception->hasResponse() && $exception->getResponse() !== null) {
                $guzzleResponse = $exception->getResponse();
            } else {
                throw ExceptionFactory::fromNetworkError(
                    message: 'Tabby API request failed due to a network error.',
                    method: $method,
                    path: $normalizedPath,
                    headers: $headers,
                    payload: $payload,
                    previous: $exception,
                );
            }
        } catch (GuzzleException $exception) {
            throw ExceptionFactory::fromNetworkError(
                message: 'Tabby API request failed due to a network error.',
                method: $method,
                path: $normalizedPath,
                headers: $headers,
                payload: $payload,
                previous: $exception,
            );
        }

        $response = new Response(
            statusCode: $guzzleResponse->getStatusCode(),
            body: (string) $guzzleResponse->getBody(),
            headers: $guzzleResponse->getHeaders(),
        );

        if (! $response->successful()) {
            throw ExceptionFactory::fromResponse(
                response: $response,
                method: $method,
                path: $normalizedPath,
                headers: $headers,
                payload: $payload,
            );
        }

        return $response;
    }

    private function normalizePath(string $path): string
    {
        return '/'.ltrim($path, '/');
    }
}
