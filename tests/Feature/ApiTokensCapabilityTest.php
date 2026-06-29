<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ApiTokensService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokensCapabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_page_is_displayed_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/api')
            ->assertOk();
    }

    public function test_api_page_requires_authentication(): void
    {
        $this->get('/api')->assertRedirect('/login');
    }

    public function test_a_user_can_create_a_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/api-tokens', [
            'name' => 'Mi integración',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('plainTextToken');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => $user->getMorphClass(),
            'name' => 'Mi integración',
        ]);
    }

    public function test_token_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/api-tokens', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_a_user_can_revoke_their_token(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokensService::class)->createTokenFor($user, 'Temporal');

        $this->actingAs($user)
            ->delete('/api-tokens/'.$token->accessToken->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_a_user_cannot_revoke_another_users_token(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $token = app(ApiTokensService::class)->createTokenFor($owner, 'De otro');

        $this->actingAs($attacker)
            ->delete('/api-tokens/'.$token->accessToken->id)
            ->assertRedirect();

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_api_v1_rejects_requests_without_a_token(): void
    {
        $this->getJson('/api/v1/user')->assertUnauthorized();
    }

    public function test_api_v1_accepts_a_valid_bearer_token(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokensService::class)->createTokenFor($user, 'API');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/user');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('error', null);
    }

    public function test_api_v1_example_resource_returns_standard_envelope(): void
    {
        $user = User::factory()->create();
        $token = app(ApiTokensService::class)->createTokenFor($user, 'API');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/example')
            ->assertOk()
            ->assertJsonStructure(['data' => ['items'], 'error']);
    }
}
