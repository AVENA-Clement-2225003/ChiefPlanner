<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_reports_ok(): void
    {
        $this->getJson('/api/health')->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_unknown_route_answers_json_not_html(): void
    {
        $this->getJson('/api/nope')->assertNotFound();
    }
}
