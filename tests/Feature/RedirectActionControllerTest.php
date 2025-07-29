<?php

namespace Tests\Feature;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RedirectActionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_redirect_to_destination_url()
    {
        $redirect = Redirect::factory()->active()->create([
            'destination_url' => 'https://google.com'
        ]);

        $response = $this->get("/r/{$redirect->code}");

        $response->assertRedirect('https://google.com');
    }

    public function test_can_redirect_with_merged_query_params()
    {
        $redirect = Redirect::factory()->active()->create([
            'destination_url' => 'https://google.com',
            'query_params' => 'utm_source=facebook&utm_campaign=ads'
        ]);

        $response = $this->get("/r/{$redirect->code}?utm_source=instagram&utm_medium=social");

        $response->assertRedirect('https://google.com?utm_source=instagram&utm_campaign=ads&utm_medium=social');
    }

    public function test_redirect_ignores_empty_query_params()
    {
        $redirect = Redirect::factory()->active()->create([
            'destination_url' => 'https://google.com',
            'query_params' => 'utm_source=facebook'
        ]);

        $response = $this->get("/r/{$redirect->code}?utm_source=&utm_campaign=test");

        $response->assertRedirect('https://google.com?utm_source=facebook&utm_campaign=test');
    }

    public function test_redirect_creates_log_entry()
    {
        $redirect = Redirect::factory()->active()->create();

        $this->get("/r/{$redirect->code}");

        $this->assertDatabaseHas('redirect_logs', [
            'redirect_id' => $redirect->id,
            'ip_address' => request()->ip()
        ]);
    }

    public function test_redirect_updates_last_accessed_at()
    {
        $redirect = Redirect::factory()->active()->create([
            'last_accessed_at' => null
        ]);

        $this->get("/r/{$redirect->code}");

        $redirect->refresh();
        $this->assertNotNull($redirect->last_accessed_at);
    }

    public function test_returns_404_for_inactive_redirect()
    {
        $redirect = Redirect::factory()->inactive()->create();

        $response = $this->get("/r/{$redirect->code}");

        $response->assertStatus(404);
    }

    public function test_returns_404_for_invalid_redirect_code()
    {
        $response = $this->get('/r/invalid-code');

        $response->assertStatus(404);
    }

    public function test_can_get_redirect_statistics()
    {
        $redirect = Redirect::factory()->create();
        
        // Create some logs with different IPs
        RedirectLog::factory()->count(5)->create([
            'redirect_id' => $redirect->id,
            'ip_address' => '192.168.1.1'
        ]);
        
        RedirectLog::factory()->count(3)->create([
            'redirect_id' => $redirect->id,
            'ip_address' => '192.168.1.2'
        ]);

        $response = $this->getJson("/api/redirects/{$redirect->code}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_accesses',
                    'unique_accesses',
                    'top_referrers',
                    'last_10_days'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(8, $data['total_accesses']);
        $this->assertEquals(2, $data['unique_accesses']);
    }

    public function test_statistics_count_unique_ips_correctly()
    {
        $redirect = Redirect::factory()->create();
        
        // Create logs with same IP
        RedirectLog::factory()->count(10)->create([
            'redirect_id' => $redirect->id,
            'ip_address' => '192.168.1.1'
        ]);

        $response = $this->getJson("/api/redirects/{$redirect->code}/stats");

        $data = $response->json('data');
        $this->assertEquals(10, $data['total_accesses']);
        $this->assertEquals(1, $data['unique_accesses']);
    }

    public function test_statistics_include_top_referrers()
    {
        $redirect = Redirect::factory()->create();
        
        RedirectLog::factory()->count(5)->create([
            'redirect_id' => $redirect->id,
            'referer' => 'https://google.com'
        ]);
        
        RedirectLog::factory()->count(3)->create([
            'redirect_id' => $redirect->id,
            'referer' => 'https://facebook.com'
        ]);

        $response = $this->getJson("/api/redirects/{$redirect->code}/stats");

        $data = $response->json('data');
        $this->assertCount(2, $data['top_referrers']);
        $this->assertEquals('https://google.com', $data['top_referrers'][0]['referer']);
        $this->assertEquals(5, $data['top_referrers'][0]['count']);
    }

    public function test_statistics_include_last_10_days()
    {
        $redirect = Redirect::factory()->create();
        
        // Create logs for today
        RedirectLog::factory()->count(5)->create([
            'redirect_id' => $redirect->id,
            'created_at' => now()
        ]);

        $response = $this->getJson("/api/redirects/{$redirect->code}/stats");

        $data = $response->json('data');
        $this->assertCount(10, $data['last_10_days']);
        
        $today = now()->format('Y-m-d');
        $todayStats = collect($data['last_10_days'])->firstWhere('date', $today);
        $this->assertEquals(5, $todayStats['total']);
        $this->assertEquals(1, $todayStats['unique']);
    }

    public function test_can_get_redirect_logs()
    {
        $redirect = Redirect::factory()->create();
        RedirectLog::factory()->count(5)->create([
            'redirect_id' => $redirect->id
        ]);

        $response = $this->getJson("/api/redirects/{$redirect->code}/logs");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'redirect_id',
                        'ip_address',
                        'user_agent',
                        'referer',
                        'query_params',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    public function test_returns_404_for_invalid_redirect_code_in_stats()
    {
        $response = $this->getJson('/api/redirects/invalid-code/stats');

        $response->assertStatus(404);
    }

    public function test_returns_404_for_invalid_redirect_code_in_logs()
    {
        $response = $this->getJson('/api/redirects/invalid-code/logs');

        $response->assertStatus(404);
    }
}
