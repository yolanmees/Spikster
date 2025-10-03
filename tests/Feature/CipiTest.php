<?php

namespace Tests\Feature;

use Tests\TestCase;

class CipiTest extends TestCase
{
    public function test_show_login_page()
    {
        $response = $this->get('/login');
        $response->assertSee('Cipi Control Panel');
        $response->assertStatus(200);
    }
}
