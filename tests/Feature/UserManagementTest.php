<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_new_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post('/utilisateurs', [
            'name' => 'Fatimetou Mint Ahmed',
            'email' => 'fatimetou@commune.mr',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role' => 'user',
            'service' => 'Etat Civil',
        ]);

        $response->assertRedirect(route('users.index'));

        $newUser = User::where('email', 'fatimetou@commune.mr')->firstOrFail();
        $this->assertSame('Etat Civil', $newUser->service);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('motdepasse123', $newUser->password));
    }

    public function test_admin_can_create_a_restricted_admin_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post('/utilisateurs', [
            'name' => 'Accueil Bis',
            'email' => 'accueil-bis@commune.mr',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role' => 'admin',
            // Aucune case de permission cochée : doit rester restreint, pas admin plein.
        ])->assertRedirect(route('users.index'));

        $newAdmin = User::where('email', 'accueil-bis@commune.mr')->firstOrFail();
        $this->assertTrue($newAdmin->isAdmin());
        $this->assertFalse($newAdmin->isUnrestrictedAdmin());
        $this->assertNull($newAdmin->service);
    }

    public function test_cannot_create_a_user_with_a_duplicate_email(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create(['email' => 'existant@commune.mr']);

        $this->actingAs($admin)->post('/utilisateurs', [
            'name' => 'Doublon Test',
            'email' => 'existant@commune.mr',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role' => 'user',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'existant@commune.mr')->count());
    }

    public function test_create_user_requires_matching_password_confirmation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post('/utilisateurs', [
            'name' => 'Test User',
            'email' => 'test-user@commune.mr',
            'password' => 'motdepasse123',
            'password_confirmation' => 'autre-chose',
            'role' => 'user',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'test-user@commune.mr']);
    }

    public function test_non_admin_cannot_create_a_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/utilisateurs', [
            'name' => 'Test User',
            'email' => 'test-user@commune.mr',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role' => 'user',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'test-user@commune.mr']);
    }

    public function test_users_index_search_filters_by_name_or_email(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->create(['name' => 'Ahmed Ould Sidi', 'email' => 'ahmed@commune.mr']);
        User::factory()->create(['name' => 'Fatimetou Mint', 'email' => 'fatimetou@commune.mr']);

        $response = $this->actingAs($admin)->get('/utilisateurs?search=Ahmed');

        $response->assertOk();
        $response->assertSee('Ahmed Ould Sidi');
        $response->assertDontSee('Fatimetou Mint');
    }

    public function test_admin_can_update_a_user_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User, 'name' => 'Jean Dupont']);

        $this->actingAs($admin)->put("/utilisateurs/{$target->id}", [
            'name' => 'Jean Dupont',
            'email' => $target->email,
            'role' => 'fatou',
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

    public function test_admin_can_reset_a_user_password(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User, 'password' => bcrypt('old-password')]);

        $this->actingAs($admin)->put("/utilisateurs/{$target->id}/mot-de-passe", [
            'password' => 'nouveau-mdp-123',
            'password_confirmation' => 'nouveau-mdp-123',
        ])->assertRedirect(route('users.index'));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('nouveau-mdp-123', $target->fresh()->password));
    }

    public function test_reset_password_requires_confirmation_match(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $target = User::factory()->create(['role' => UserRole::User, 'password' => bcrypt('old-password')]);

        $this->actingAs($admin)->put("/utilisateurs/{$target->id}/mot-de-passe", [
            'password' => 'nouveau-mdp-123',
            'password_confirmation' => 'autre-chose',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('old-password', $target->fresh()->password));
    }

    public function test_non_admin_cannot_reset_a_user_password(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $target = User::factory()->create(['role' => UserRole::User, 'password' => bcrypt('old-password')]);

        $this->actingAs($user)->put("/utilisateurs/{$target->id}/mot-de-passe", [
            'password' => 'nouveau-mdp-123',
            'password_confirmation' => 'nouveau-mdp-123',
        ])->assertForbidden();
    }

    public function test_creating_a_user_is_logged_in_the_audit_log(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Admin Principal']);

        $this->actingAs($admin)->post('/utilisateurs', [
            'name' => 'Fatimetou Mint Ahmed',
            'email' => 'fatimetou-audit@commune.mr',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
            'role' => 'user',
            'service' => 'Etat Civil',
        ]);

        $newUser = \App\Models\User::where('email', 'fatimetou-audit@commune.mr')->firstOrFail();
        $this->assertDatabaseHas('user_audit_logs', [
            'actor_name' => 'Admin Principal',
            'target_user_id' => $newUser->id,
            'target_name' => 'Fatimetou Mint Ahmed',
            'action' => 'created',
        ]);
    }

    public function test_updating_a_user_is_logged_in_the_audit_log(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Admin Principal']);
        $target = User::factory()->create(['role' => UserRole::User, 'name' => 'Jean Dupont']);

        $this->actingAs($admin)->put("/utilisateurs/{$target->id}", [
            'name' => 'Jean Dupont',
            'email' => $target->email,
            'role' => 'fatou',
        ]);

        $this->assertDatabaseHas('user_audit_logs', [
            'actor_name' => 'Admin Principal',
            'target_user_id' => $target->id,
            'target_name' => 'Jean Dupont',
            'action' => 'updated',
        ]);
    }

    public function test_resetting_a_password_is_logged_in_the_audit_log(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Admin Principal']);
        $target = User::factory()->create(['role' => UserRole::User, 'name' => 'Jean Dupont']);

        $this->actingAs($admin)->put("/utilisateurs/{$target->id}/mot-de-passe", [
            'password' => 'nouveau-mdp-123',
            'password_confirmation' => 'nouveau-mdp-123',
        ]);

        $this->assertDatabaseHas('user_audit_logs', [
            'actor_name' => 'Admin Principal',
            'target_user_id' => $target->id,
            'target_name' => 'Jean Dupont',
            'action' => 'password_reset',
        ]);
    }

    public function test_deleting_a_user_is_logged_and_survives_the_deletion(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Admin Principal']);
        $target = User::factory()->create(['role' => UserRole::User, 'name' => 'Jean Dupont']);
        $targetId = $target->id;

        $this->actingAs($admin)->delete("/utilisateurs/{$target->id}");

        $this->assertDatabaseMissing('users', ['id' => $targetId]);
        $this->assertDatabaseHas('user_audit_logs', [
            'actor_name' => 'Admin Principal',
            'target_user_id' => $targetId,
            'target_name' => 'Jean Dupont',
            'action' => 'deleted',
        ]);

        // Le journal doit rester lisible malgre la suppression du compte cible.
        $response = $this->actingAs($admin)->get('/utilisateurs/journal');
        $response->assertOk();
        $response->assertSee('Jean Dupont');
    }

    public function test_non_admin_cannot_view_the_audit_log(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->get('/utilisateurs/journal')->assertForbidden();
    }

    public function test_audit_log_search_filters_by_target_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Admin Principal']);
        $targetA = User::factory()->create(['role' => UserRole::User, 'name' => 'Jean Dupont']);
        $targetB = User::factory()->create(['role' => UserRole::User, 'name' => 'Autre Personne']);

        $this->actingAs($admin)->put("/utilisateurs/{$targetA->id}/mot-de-passe", [
            'password' => 'nouveau-mdp-123',
            'password_confirmation' => 'nouveau-mdp-123',
        ]);
        $this->actingAs($admin)->put("/utilisateurs/{$targetB->id}/mot-de-passe", [
            'password' => 'nouveau-mdp-123',
            'password_confirmation' => 'nouveau-mdp-123',
        ]);

        $response = $this->actingAs($admin)->get('/utilisateurs/journal?search=Jean');

        $response->assertOk();
        $response->assertSee('Jean Dupont');
        $response->assertDontSee('Autre Personne');
    }
}
