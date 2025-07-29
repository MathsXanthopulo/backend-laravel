<?php

namespace Tests\Feature;

use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RedirectControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_list_redirects()
    {
        $redirects = Redirect::factory()->count(3)->create();

        $response = $this->getJson('/api/redirects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'status',
                        'destination_url',
                        'last_accessed_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_create_redirect_with_valid_url()
    {
        Http::fake([
            'https://google.com' => Http::response('', 200)
        ]);

        $data = [
            'destination_url' => 'https://google.com'
        ];

        $response = $this->postJson('/api/redirects', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'code',
                    'destination_url',
                    'query_params',
                    'is_active'
                ]
            ]);

        $this->assertDatabaseHas('redirects', [
            'destination_url' => 'https://google.com',
            'is_active' => true
        ]);
    }

    public function test_can_create_redirect_with_query_params()
    {
        Http::fake([
            'https://google.com' => Http::response('', 200)
        ]);

        $data = [
            'destination_url' => 'https://google.com?utm_source=facebook&utm_campaign=ads'
        ];

        $response = $this->postJson('/api/redirects', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('redirects', [
            'destination_url' => 'https://google.com',
            'query_params' => 'utm_source=facebook&utm_campaign=ads'
        ]);
    }

    public function test_cannot_create_redirect_with_http_url()
    {
        $data = [
            'destination_url' => 'http://google.com'
        ];

        $response = $this->postJson('/api/redirects', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination_url']);
    }

    public function test_cannot_create_redirect_with_invalid_url()
    {
        $data = [
            'destination_url' => 'invalid-url'
        ];

        $response = $this->postJson('/api/redirects', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination_url']);
    }

    public function test_cannot_create_redirect_with_own_application_url()
    {
        $data = [
            'destination_url' => config('app.url') . '/some-path'
        ];

        $response = $this->postJson('/api/redirects', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination_url']);
    }

    public function test_cannot_create_redirect_with_invalid_status_code()
    {
        Http::fake([
            'https://google.com' => Http::response('', 404)
        ]);

        $data = [
            'destination_url' => 'https://google.com'
        ];

        $response = $this->postJson('/api/redirects', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination_url']);
    }

    public function test_can_show_redirect()
    {
        $redirect = Redirect::factory()->create();

        $response = $this->getJson("/api/redirects/{$redirect->code}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'code',
                    'destination_url',
                    'query_params',
                    'is_active',
                    'last_accessed_at',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_returns_404_for_invalid_redirect_code()
    {
        $response = $this->getJson('/api/redirects/invalid-code');

        $response->assertStatus(404);
    }

    public function test_can_update_redirect()
    {
        Http::fake([
            'https://facebook.com' => Http::response('', 200)
        ]);

        $redirect = Redirect::factory()->create();
        $data = [
            'destination_url' => 'https://facebook.com',
            'is_active' => false
        ];

        $response = $this->putJson("/api/redirects/{$redirect->code}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'code',
                    'destination_url',
                    'query_params',
                    'is_active'
                ]
            ]);

        $this->assertDatabaseHas('redirects', [
            'id' => $redirect->id,
            'destination_url' => 'https://facebook.com',
            'is_active' => false
        ]);
    }

    public function test_can_delete_redirect()
    {
        $redirect = Redirect::factory()->create();

        $response = $this->deleteJson("/api/redirects/{$redirect->code}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('redirects', ['id' => $redirect->id]);
        $this->assertDatabaseHas('redirects', [
            'id' => $redirect->id,
            'is_active' => false
        ]);
    }
}
