<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Exceptions;

use RuntimeException;
use Throwable;

class TabbyException extends RuntimeException
{
    protected ?int $statusCode = null;

    /** @var array<string, mixed>|null */
    protected ?array $responseJson = null;

    protected ?string $responseBody = null;

    protected ?string $requestMethod = null;

    protected ?string $requestPath = null;

    /** @var array<string, mixed> */
    protected array $requestHeaders = [];

    /** @var array<string, mixed> */
    protected array $requestPayload = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponseJson(): ?array
    {
        return $this->responseJson;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function getRequestPath(): ?string
    {
        return $this->requestPath;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequestPayload(): array
    {
        return $this->requestPayload;
    }

    /**
     * @param array<string, mixed>|null $responseJson
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $payload
     */
    public function withContext(
        ?int $statusCode = null,
        ?string $responseBody = null,
        ?array $responseJson = null,
        ?string $requestMethod = null,
        ?string $requestPath = null,
        array $headers = [],
        array $payload = [],
    ): static {
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
        $this->responseJson = $responseJson;
        $this->requestMethod = $requestMethod;
        $this->requestPath = $requestPath;
        $this->requestHeaders = $headers;
        $this->requestPayload = $payload;

        return $this;
    }
}
