<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Affectation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffectationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_create_an_affectation(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'can_affectation' => true]);

        $response = $this->actingAs($user)->post('/affectation', [
            'sevice' => 'Etat Civil',
            'iddmd' => '1',
            'dateaff' => now()->format('Y-m-d'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('affectation', 0);
    }

    public function test_admin_creating_an_affectation_also_notifies_the_service(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post('/affectation', [
            'sevice' => 'Etat Civil',
            'iddmd' => '1',
            'dateaff' => now()->format('Y-m-d'),
        ])->assertRedirect(route('affectation.index'));

        $this->assertDatabaseHas('affectation', ['sevice' => 'Etat Civil', 'iddmd' => '1']);
        $this->assertDatabaseHas('service_notifications', ['service' => 'Etat Civil']);
    }

    public function test_user_without_flag_cannot_view_affectation_index(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'can_affectation' => false]);

        $this->actingAs($user)->get('/affectation')->assertForbidden();
    }

    public function test_user_with_flag_can_view_affectation_index(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'can_affectation' => true]);

        $this->actingAs($user)->get('/affectation')->assertOk();
    }

    public function test_user_without_flag_cannot_update_or_destroy_an_affectation(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'can_affectation' => false]);
        $affectation = Affectation::create(['sevice' => 'Etat Civil', 'iddmd' => '1', 'dateaff' => now()->format('Y-m-d')]);

        $this->actingAs($user)
            ->put("/affectation/{$affectation->id}", ['sevice' => 'Urbanisme', 'iddmd' => '1', 'dateaff' => now()->format('Y-m-d')])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete("/affectation/{$affectation->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('affectation', ['id' => $affectation->id, 'sevice' => 'Etat Civil']);
    }

    public function test_only_admin_can_change_the_service_of_an_affectation(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'can_affectation' => true, 'service' => 'Etat Civil']);
        $affectation = Affectation::create(['sevice' => 'Etat Civil', 'iddmd' => '1', 'dateaff' => now()->format('Y-m-d')]);

        $this->actingAs($serviceUser)->put("/affectation/{$affectation->id}", [
            'sevice' => 'Urbanisme',
            'iddmd' => '1',
            'dateaff' => now()->format('Y-m-d'),
        ])->assertRedirect(route('affectation.index'));

        // La tentative de transfert vers un autre service est silencieusement ignorée pour un non-admin.
        $this->assertSame('Etat Civil', $affectation->fresh()->sevice);
    }

    public function test_service_user_only_sees_affectations_for_their_own_service(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'can_affectation' => true, 'service' => 'Etat Civil']);
        Affectation::create(['sevice' => 'Etat Civil', 'iddmd' => '1', 'dateaff' => now()->format('Y-m-d')]);
        Affectation::create(['sevice' => 'Urbanisme', 'iddmd' => '2', 'dateaff' => now()->format('Y-m-d')]);

        $response = $this->actingAs($serviceUser)->get('/affectation');

        $response->assertViewHas('affectation', function ($affectations) {
            return $affectations->every(fn ($a) => $a->sevice === 'Etat Civil');
        });
    }
}
