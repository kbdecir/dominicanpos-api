<?php

namespace Tests\Feature\Api\V1;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_company(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Empresa Demo',
            'business_name' => 'Empresa Demo SRL',
            'rnc' => '132456789',
            'email' => 'info@empresa.com',
            'phone' => '8095551111',
            'address' => 'Santo Domingo',
        ];

        $response = $this->postJson('/api/v1/companies', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Empresa creada correctamente.',
                'name' => 'Empresa Demo',
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Empresa Demo',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('company_user', [
            'user_id' => $user->id,
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_list_only_their_companies(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $companyA = Company::factory()->create([
            'created_by' => $userA->id,
            'updated_by' => $userA->id,
        ]);

        $companyB = Company::factory()->create([
            'created_by' => $userB->id,
            'updated_by' => $userB->id,
        ]);

        $companyA->users()->attach($userA->id, ['role' => 'owner', 'is_active' => true]);
        $companyB->users()->attach($userB->id, ['role' => 'owner', 'is_active' => true]);

        Sanctum::actingAs($userA);

        $response = $this->getJson('/api/v1/companies');

        $response->assertOk()
            ->assertJsonFragment(['id' => $companyA->id])
            ->assertJsonMissing(['id' => $companyB->id]);
    }

    public function test_user_cannot_view_company_that_does_not_belong_to_him(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $company = Company::factory()->create([
            'created_by' => $userB->id,
            'updated_by' => $userB->id,
        ]);

        $company->users()->attach($userB->id, ['role' => 'owner', 'is_active' => true]);

        Sanctum::actingAs($userA);

        $response = $this->getJson("/api/v1/companies/{$company->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_update_their_company(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $company->users()->attach($user->id, ['role' => 'owner', 'is_active' => true]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/companies/{$company->id}", [
            'name' => 'Empresa Editada',
            'phone' => '8095552222',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Empresa actualizada correctamente.',
                'name' => 'Empresa Editada',
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Empresa Editada',
            'phone' => '8095552222',
        ]);
    }

    public function test_user_can_deactivate_their_company(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $company->users()->attach($user->id, ['role' => 'owner', 'is_active' => true]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/v1/companies/{$company->id}/deactivate");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Empresa inactivada correctamente.',
                'is_active' => false,
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'is_active' => false,
        ]);
    }
}
