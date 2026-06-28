<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guests_can_see_the_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
