<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_a_user_role_and_grant_affectation_access(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User, 'name' => 'Jean Dupont']);

        $this->actingAs($admin)->put("/utilisateurs/{$target->id}", [
            'name' => 'Jean Dupont',
            'email' => $target->email,
            'role' => 'fatou',
            'can_affectation' => '1',
        ])->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertTrue($target->isFatou());
        // Le service est vidé pour les rôles fatou/maire/admin (pas pertinent pour eux).
        $this->assertNull($target->service);
    }

    public function test_cannot_demote_the_last_remaining_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Jean Dupont']);

        $response = $this->actingAs($admin)->put("/utilisateurs/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'user',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->delete("/utilisateurs/{$admin->id}")
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_cannot_delete_another_admin_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $otherAdmin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->delete("/utilisateurs/{$otherAdmin->id}")
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_admin_can_delete_a_regular_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($admin)->delete("/utilisateurs/{$target->id}")
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }
}
