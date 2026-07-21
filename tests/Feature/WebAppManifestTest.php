<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebAppManifestTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
    }
}
