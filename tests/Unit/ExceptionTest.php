<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Exceptions\ApiException;
use MustafaTaj\Tabby\Exceptions\AuthenticationException;
use MustafaTaj\Tabby\Exceptions\ExceptionFactory;
use MustafaTaj\Tabby\Exceptions\NetworkException;
use MustafaTaj\Tabby\Exceptions\ValidationException;
use MustafaTaj\Tabby\Http\Response;
use MustafaTaj\Tabby\Support\Sanitizer;
use MustafaTaj\Tabby\Tests\TestCase;

final class ExceptionTest extends TestCase
{
    public function test_401_maps_to_authentication_exception(): void
    {
        $exception = ExceptionFactory::fromResponse(
            new Response(401, '{"error":"Unauthorized"}'),
            'GET',
            '/api/v2/payments/1',
            ['Authorization' => 'Bearer sk_test_secret'],
        );

        $this->assertInstanceOf(AuthenticationException::class, $exception);
        $this->assertSame(401, $exception->getStatusCode());
    }

    public function test_403_maps_to_authentication_exception(): void
    {
        $exception = ExceptionFactory::fromResponse(
            new Response(403, '{}'),
            'GET',
            '/api/v2/payments/1',
        );

        $this->assertInstanceOf(AuthenticationException::class, $exception);
    }

    public function test_400_maps_to_validation_exception(): void
    {
        $exception = ExceptionFactory::fromResponse(
            new Response(400, '{"message":"Invalid request"}'),
            'POST',
            '/api/v2/checkout',
        );

        $this->assertInstanceOf(ValidationException::class, $exception);
    }

    public function test_422_maps_to_validation_exception(): void
    {
        $exception = ExceptionFactory::fromResponse(
            new Response(422, '{}'),
            'POST',
            '/api/v2/checkout',
        );

        $this->assertInstanceOf(ValidationException::class, $exception);
    }

    public function test_500_maps_to_api_exception(): void
    {
        $exception = ExceptionFactory::fromResponse(
            new Response(500, '{}'),
            'GET',
            '/api/v2/payments/1',
        );

        $this->assertInstanceOf(ApiException::class, $exception);
    }

    public function test_network_exception_factory(): void
    {
        $exception = ExceptionFactory::fromNetworkError(
            message: 'Timeout',
            method: 'GET',
            path: '/api/v2/payments/1',
        );

        $this->assertInstanceOf(NetworkException::class, $exception);
    }

    public function test_secret_key_is_sanitized_in_exception_context(): void
    {
        $exception = ExceptionFactory::fromResponse(
            new Response(401, '{}'),
            'GET',
            '/api/v2/payments/1',
            ['Authorization' => 'Bearer sk_test_super_secret_key_value'],
            ['secret_key' => 'sk_test_super_secret_key_value'],
        );

        $this->assertStringNotContainsString('sk_test_super_secret_key_value', $exception->getMessage());
        $this->assertStringContainsString('***', (string) $exception->getRequestHeaders()['Authorization']);
        $this->assertStringContainsString('***', (string) $exception->getRequestPayload()['secret_key']);
    }

    public function test_sanitizer_redacts_authorization_header(): void
    {
        $sanitized = Sanitizer::headers([
            'Authorization' => 'Bearer sk_test_abcdefghijklmnop',
        ]);

        $this->assertSame('Bear***mnop', $sanitized['Authorization']);
    }
}
