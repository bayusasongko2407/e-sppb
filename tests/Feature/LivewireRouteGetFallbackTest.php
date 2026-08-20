<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LivewireRouteGetFallbackTest extends TestCase
{
    public function test_get_request_to_livewire_update_route_redirects_safely_to_referer(): void
    {
        $response = $this->withHeaders([
            'referer' => 'https://e-sppb.engiboard.web.id/document-accesses/test-hash/edit',
        ])->get('/livewire-legacy123/update');

        $response->assertRedirect('https://e-sppb.engiboard.web.id/document-accesses/test-hash/edit');
    }

    public function test_get_request_to_standard_livewire_update_redirects_safely(): void
    {
        $response = $this->withHeaders([
            'referer' => 'https://e-sppb.engiboard.web.id/sppb-headers',
        ])->get('/livewire/update');

        $response->assertRedirect('https://e-sppb.engiboard.web.id/sppb-headers');
    }

    public function test_post_request_to_legacy_hashed_livewire_update_redirects_307_to_fixed_livewire_update(): void
    {
        $response = $this->post('/livewire-legacy123/update');

        $response->assertStatus(307);
        $response->assertRedirect('/livewire/update');
    }
}
