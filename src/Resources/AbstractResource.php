<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Resources;

use MustafaTaj\Tabby\Config\TabbyConfig;
use MustafaTaj\Tabby\Contracts\HttpClientInterface;
use MustafaTaj\Tabby\Http\Response;

abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClientInterface $http,
        protected readonly TabbyConfig $config,
    ) {
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(bool $withJsonContentType = false): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->config->getSecretKey(),
            'Accept' => 'application/json',
        ];

        if ($withJsonContentType) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }

    /**
     * @return array<string, string>
     */
    protected function webhookHeaders(bool $withJsonContentType = false): array
    {
        return array_merge($this->defaultHeaders($withJsonContentType), [
            'X-Merchant-Code' => $this->config->getMerchantCode(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decode(Response $response): array
    {
        return $response->json() ?? [];
    }
}
