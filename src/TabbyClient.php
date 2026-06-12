<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby;

use MustafaTaj\Tabby\Config\TabbyConfig;
use MustafaTaj\Tabby\Contracts\HttpClientInterface;
use MustafaTaj\Tabby\Http\GuzzleHttpClient;
use MustafaTaj\Tabby\Resources\CheckoutResource;
use MustafaTaj\Tabby\Resources\PaymentResource;
use MustafaTaj\Tabby\Resources\WebhookResource;

final class TabbyClient
{
    private ?CheckoutResource $checkout = null;

    private ?PaymentResource $payments = null;

    private ?WebhookResource $webhooks = null;

    public function __construct(
        private readonly TabbyConfig $config,
        private readonly HttpClientInterface $http,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromConfig(array $config, ?HttpClientInterface $http = null): self
    {
        $tabbyConfig = TabbyConfig::fromArray($config);

        return new self(
            config: $tabbyConfig,
            http: $http ?? new GuzzleHttpClient($tabbyConfig),
        );
    }

    public function checkout(): CheckoutResource
    {
        return $this->checkout ??= new CheckoutResource($this->http, $this->config);
    }

    public function payments(): PaymentResource
    {
        return $this->payments ??= new PaymentResource($this->http, $this->config);
    }

    public function webhooks(): WebhookResource
    {
        return $this->webhooks ??= new WebhookResource($this->http, $this->config);
    }

    public function getConfig(): TabbyConfig
    {
        return $this->config;
    }
}
