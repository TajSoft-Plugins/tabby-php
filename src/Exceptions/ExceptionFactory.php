<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Exceptions;

use MustafaTaj\Tabby\Http\Response;
use MustafaTaj\Tabby\Support\Sanitizer;

final class ExceptionFactory
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     */
    public static function fromResponse(
        Response $response,
        string $method,
        string $path,
        array $headers = [],
        array $payload = [],
    ): TabbyException {
        $sanitizedHeaders = Sanitizer::headers($headers);
        $sanitizedPayload = Sanitizer::payload($payload);
        $status = $response->status();
        $body = $response->body();
        $json = $response->json();

        $message = self::buildMessage($status, $json, $body);

        $exception = match (true) {
            $status === 401 || $status === 403 => new AuthenticationException($message, $status),
            $status === 400 || $status === 422 => new ValidationException($message, $status),
            default => new ApiException($message, $status),
        };

        return $exception->withContext(
            statusCode: $status,
            responseBody: $body,
            responseJson: $json,
            requestMethod: $method,
            requestPath: $path,
            headers: $sanitizedHeaders,
            payload: $sanitizedPayload,
        );
    }

    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     */
    public static function fromNetworkError(
        string $message,
        string $method,
        string $path,
        array $headers = [],
        array $payload = [],
        ?\Throwable $previous = null,
    ): NetworkException {
        return (new NetworkException($message, 0, $previous))->withContext(
            requestMethod: $method,
            requestPath: $path,
            headers: Sanitizer::headers($headers),
            payload: Sanitizer::payload($payload),
        );
    }

    /**
     * @param array<string, mixed>|null $json
     */
    private static function buildMessage(int $status, ?array $json, string $body): string
    {
        if (is_array($json)) {
            foreach (['error', 'message', 'detail'] as $key) {
                if (isset($json[$key]) && is_string($json[$key]) && $json[$key] !== '') {
                    return sprintf('Tabby API request failed with status %d: %s', $status, $json[$key]);
                }
            }
        }

        if ($body !== '') {
            return sprintf('Tabby API request failed with status %d.', $status);
        }

        return sprintf('Tabby API request failed with status %d.', $status);
    }
}
