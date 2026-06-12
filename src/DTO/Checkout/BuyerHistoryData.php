<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class BuyerHistoryData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly ?string $registeredSince = null,
        public readonly ?int $loyaltyLevel = null,
        public readonly ?int $wishlistCount = null,
        public readonly ?bool $isSocialNetworksConnected = null,
        public readonly ?bool $isPhoneNumberVerified = null,
        public readonly ?bool $isEmailVerified = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter([
            'registered_since' => $this->registeredSince,
            'loyalty_level' => $this->loyaltyLevel,
            'wishlist_count' => $this->wishlistCount,
            'is_social_networks_connected' => $this->isSocialNetworksConnected,
            'is_phone_number_verified' => $this->isPhoneNumberVerified,
            'is_email_verified' => $this->isEmailVerified,
        ], static fn (mixed $value): bool => $value !== null);

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
