<?php

namespace QUI\Apple;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use QUI\Permissions\Exception;

class AppleTest extends TestCase
{
    public function testGetProfileDataReturnsDecodedPayload(): void
    {
        $payload = [
            'sub' => 'apple-sub-123',
            'email' => 'user@example.com',
            'email_verified' => 'true',
            'iss' => 'https://appleid.apple.com',
        ];

        $token = $this->buildToken($payload);
        $result = Apple::getProfileData($token);

        $this->assertSame($payload['sub'], $result['sub']);
        $this->assertSame($payload['email'], $result['email']);
        $this->assertSame($payload['email_verified'], $result['email_verified']);
        $this->assertSame($payload['iss'], $result['iss']);
    }

    #[DataProvider('invalidTokenProvider')]
    public function testGetProfileDataThrowsExceptionForInvalidToken(string $token): void
    {
        $this->expectException(Exception::class);
        Apple::getProfileData($token);
    }

    public static function invalidTokenProvider(): array
    {
        return [
            'missing_payload_part' => ['header-only'],
            'empty_payload_part' => ['header..signature'],
            'invalid_base64_payload' => ['header.###.signature'],
            'invalid_json_payload' => ['header.' . self::base64UrlEncode('not-json') . '.signature'],
        ];
    }

    private function buildToken(array $payload): string
    {
        $header = [
            'alg' => 'RS256',
            'kid' => 'unit-test-kid',
            'typ' => 'JWT',
        ];

        return self::base64UrlEncode(json_encode($header))
            . '.'
            . self::base64UrlEncode(json_encode($payload))
            . '.signature';
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
