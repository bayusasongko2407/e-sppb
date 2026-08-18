<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_page_is_publicly_accessible(): void
    {
        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertSee('Kebijakan Privasi (Privacy Policy)');
        $response->assertSee('E-SPPB ENTERPRISE');
    }

    public function test_kebijakan_privasi_alias_is_publicly_accessible(): void
    {
        $response = $this->get('/kebijakan-privasi');

        $response->assertStatus(200);
        $response->assertSee('Kebijakan Privasi (Privacy Policy)');
    }
}
