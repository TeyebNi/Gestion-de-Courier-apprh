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

    public function test_admin_without_can_manage_users_keeps_other_admin_access(): void
    {
        $restrictedAdmin = User::factory()->create(['role' => UserRole::Admin, 'can_manage_users' => false]);

        $this->actingAs($restrictedAdmin)->get('/utilisateurs')->assertForbidden();
        $this->actingAs($restrictedAdmin)->get('/orientation')->assertOk();
        $this->actingAs($restrictedAdmin)->get('/typedem')->assertOk();
        $this->actingAs($restrictedAdmin)->get('/depot')->assertOk();
    }

    public function test_cannot_remove_the_last_user_manager(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Jean Dupont', 'can_manage_users' => true]);

        $response = $this->actingAs($admin)->put("/utilisateurs/{$admin->id}", [
            'name' => 'Jean Dupont',
            'email' => $admin->email,
            'role' => 'admin',
            'can_manage_users' => '0',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue($admin->fresh()->can_manage_users);
    }

    public function test_can_remove_can_manage_users_when_another_admin_still_has_it(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'can_manage_users' => true]);
        $target = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Jean Dupont', 'can_manage_users' => true]);

        $this->actingAs($admin)->put("/utilisateurs/{$target->id}", [
            'name' => 'Jean Dupont',
            'email' => $target->email,
            'role' => 'admin',
            'can_manage_users' => '0',
        ])->assertRedirect(route('users.index'));

        $this->assertFalse($target->fresh()->can_manage_users);
    }

    public function test_restricted_admin_like_accueil_loses_cabinet_maire_and_all_services_access(): void
    {
        $accueilLikeAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'can_access_cabinet' => false,
            'can_access_maire' => false,
            'can_access_all_services' => false,
        ]);

        $this->actingAs($accueilLikeAdmin)->get('/circuit/fatou')->assertForbidden();
        $this->actingAs($accueilLikeAdmin)->get('/circuit/maire')->assertForbidden();
        // Toujours admin : garde Orientation/Typedem/Dépôt.
        $this->actingAs($accueilLikeAdmin)->get('/orientation')->assertOk();
        $this->actingAs($accueilLikeAdmin)->get('/depot')->assertOk();
    }

    public function test_restricted_admin_sees_the_minimal_accueil_dashboard(): void
    {
        $accueilLikeAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'can_manage_users' => false,
            'can_access_cabinet' => false,
            'can_access_maire' => false,
            'can_access_all_services' => false,
        ]);

        $response = $this->actingAs($accueilLikeAdmin)->get('/');

        $response->assertOk();
        $response->assertSee('Guichet');
    }

    public function test_unrestricted_admin_still_sees_the_full_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertDontSee('Guichet');
        $response->assertSee('Nombre de Demandes');
    }

    public function test_dashboard_does_not_leak_raw_demande_list_to_cabinet_or_maire(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);
        $maire = User::factory()->create(['role' => UserRole::Maire]);

        $cabinetResponse = $this->actingAs($cabinet)->get('/');
        $cabinetResponse->assertOk();
        $cabinetResponse->assertSee('En attente au Cabinet');
        $cabinetResponse->assertDontSee('Dernières Demandes Déposées');
        $cabinetResponse->assertDontSee(route('depot.index'), false);

        $maireResponse = $this->actingAs($maire)->get('/');
        $maireResponse->assertOk();
        $maireResponse->assertSee('En attente de décision');
        $maireResponse->assertDontSee('Dernières Demandes Déposées');
    }
}
