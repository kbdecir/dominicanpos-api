<?php

namespace Tests\Feature\Api\V1;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_branch_in_their_company(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $company->users()->attach($user->id, ['role' => 'owner', 'is_active' => true]);

        Sanctum::actingAs($user);

        $payload = [
            'company_id' => $company->id,
            'name' => 'Sucursal Naco',
            'code' => 'NACO-01',
            'email' => 'naco@empresa.com',
            'phone' => '8095553333',
            'address' => 'Av. Tiradentes',
            'city' => 'Santo Domingo',
            'sector' => 'Naco',
            'is_main' => true,
        ];

        $response = $this->postJson('/api/v1/branches', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Sucursal creada correctamente.',
                'name' => 'Sucursal Naco',
            ]);

        $this->assertDatabaseHas('branches', [
            'company_id' => $company->id,
            'name' => 'Sucursal Naco',
            'is_main' => true,
        ]);
    }

    public function test_user_cannot_create_branch_in_company_that_does_not_belong_to_him(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $company = Company::factory()->create([
            'created_by' => $userB->id,
            'updated_by' => $userB->id,
        ]);

        $company->users()->attach($userB->id, ['role' => 'owner', 'is_active' => true]);

        Sanctum::actingAs($userA);

        $response = $this->postJson('/api/v1/branches', [
            'company_id' => $company->id,
            'name' => 'Sucursal Bloqueada',
        ]);

        $response->assertStatus(404);
    }

    public function test_user_cannot_create_branch_in_inactive_company(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'is_active' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $company->users()->attach($user->id, ['role' => 'owner', 'is_active' => true]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/branches', [
            'company_id' => $company->id,
            'name' => 'Sucursal Prohibida',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'No se pueden crear sucursales en una empresa inactiva.',
            ]);
    }

    public function test_user_can_list_branches_by_company(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $company->users()->attach($user->id, ['role' => 'owner', 'is_active' => true]);

        Branch::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Sucursal 1',
        ]);

        Branch::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Sucursal 2',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/companies/{$company->id}/branches");

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Sucursal 1'])
            ->assertJsonFragment(['name' => 'Sucursal 2']);
    }

    public function test_only_one_branch_can_be_main_per_company(): void
    {
        $user = User::factory()->create();

        $company = Company::factory()->create([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $company->users()->attach($user->id, ['role' => 'owner', 'is_active' => true]);

        $branch1 = Branch::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'is_main' => true,
        ]);

        $branch2 = Branch::factory()->create([
            'company_id' => $company->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'is_main' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/branches/{$branch2->id}", [
            'is_main' => true,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('branches', [
            'id' => $branch1->id,
            'is_main' => false,
        ]);

        $this->assertDatabaseHas('branches', [
            'id' => $branch2->id,
            'is_main' => true,
        ]);
    }
}
