<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_plain_user_cannot_access_fatou_pages(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->get('/circuit/fatou')->assertForbidden();
    }

    public function test_fatou_can_access_fatou_pages(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);

        $this->actingAs($fatou)->get('/circuit/fatou')->assertOk();
    }

    public function test_plain_user_cannot_access_maire_pages(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->get('/circuit/maire')->assertForbidden();
    }

    public function test_non_admin_cannot_access_admin_config_pages(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);

        $this->actingAs($fatou)->get('/utilisateurs')->assertForbidden();
    }

    public function test_admin_can_access_every_role_gated_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get('/circuit/fatou')->assertOk();
        $this->actingAs($admin)->get('/circuit/maire')->assertOk();
        $this->actingAs($admin)->get('/utilisateurs')->assertOk();
    }

    public function test_can_access_affectation_follows_admin_or_flag(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'can_affectation' => false]);
        $plainUser = User::factory()->create(['role' => UserRole::User, 'can_affectation' => false]);
        $grantedUser = User::factory()->create(['role' => UserRole::User, 'can_affectation' => true]);

        $this->assertTrue($admin->canAccessAffectation());
        $this->assertFalse($plainUser->canAccessAffectation());
        $this->assertTrue($grantedUser->canAccessAffectation());
    }
}
