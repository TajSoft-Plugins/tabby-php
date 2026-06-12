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
     * Checkout session requests authenticate with the public key.
     *
     * @return array<string, string>
     */
    protected function checkoutHeaders(bool $withJsonContentType = false): array
    {
        return $this->authHeaders($this->config->getPublicKey(), $withJsonContentType);
    }

    /**
     * Payment requests authenticate with the secret key.
     *
     * @return array<string, string>
     */
    protected function secretHeaders(bool $withJsonContentType = false): array
    {
        return $this->authHeaders($this->config->getSecretKey(), $withJsonContentType);
    }

    /**
     * @return array<string, string>
     */
    protected function webhookHeaders(bool $withJsonContentType = false): array
    {
        return array_merge($this->secretHeaders($withJsonContentType), [
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

    /**
     * @return array<string, string>
     */
    private function authHeaders(string $token, bool $withJsonContentType): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];

        if ($withJsonContentType) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }
}
