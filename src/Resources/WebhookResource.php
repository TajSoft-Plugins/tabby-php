<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Resources;

use MustafaTaj\Tabby\DTO\Webhook\RegisterWebhookData;
use MustafaTaj\Tabby\DTO\Webhook\UpdateWebhookData;

final class WebhookResource extends AbstractResource
{
    /**
     * @param array<string, string>|null $header
     * @return array<string, mixed>
     */
    public function register(string $url, ?array $header = null): array
    {
        return $this->registerWithData(new RegisterWebhookData(
            url: $url,
            header: $header,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function registerWithData(RegisterWebhookData $data): array
    {
        $response = $this->http->post(
            '/api/v1/webhooks',
            $data->toArray(),
            $this->webhookHeaders(true),
        );

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $response = $this->http->get(
            '/api/v1/webhooks',
            [],
            $this->webhookHeaders(),
        );

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieve(string $webhookId): array
    {
        $response = $this->http->get(
            sprintf('/api/v1/webhooks/%s', rawurlencode($webhookId)),
            [],
            $this->webhookHeaders(),
        );

        return $this->decode($response);
    }

    /**
     * @param array<string, mixed>|UpdateWebhookData $payload
     * @return array<string, mixed>
     */
    public function update(string $webhookId, array|UpdateWebhookData $payload): array
    {
        $data = $payload instanceof UpdateWebhookData ? $payload->toArray() : $payload;

        $response = $this->http->put(
            sprintf('/api/v1/webhooks/%s', rawurlencode($webhookId)),
            $data,
            $this->webhookHeaders(true),
        );

        return $this->decode($response);
    }

    /**
     * @return array<string, mixed>|bool
     */
    public function delete(string $webhookId): array|bool
    {
        $response = $this->http->delete(
            sprintf('/api/v1/webhooks/%s', rawurlencode($webhookId)),
            $this->webhookHeaders(),
        );

        $json = $response->json();

        return $json ?? true;
    }
}
