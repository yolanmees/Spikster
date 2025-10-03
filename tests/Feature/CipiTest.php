<?php

namespace Tests\Feature;

use Tests\TestCase;

class CipiTest extends TestCase
{
    public function test_show_login_page()
    {
        $response = $this->get('/login');

        // Just verify the login page loads successfully
        // The actual content is JavaScript-rendered, so we just check the status
        $response->assertStatus(200);
    }
}
