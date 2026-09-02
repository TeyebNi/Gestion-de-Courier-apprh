<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Orientation;
use App\Models\Typedem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_orientation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post('/orientation', ['name' => 'Etat Civil'])
            ->assertRedirect(route('orientation.index'));

        $this->assertDatabaseHas('orientation', ['name' => 'Etat Civil']);
    }

    public function test_non_admin_cannot_create_an_orientation(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)
            ->post('/orientation', ['name' => 'Etat Civil'])
            ->assertForbidden();

        $this->assertDatabaseCount('orientation', 0);
    }

    public function test_admin_can_update_and_delete_an_orientation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $orientation = Orientation::create(['name' => 'Etat Civil']);

        $this->actingAs($admin)
            ->put("/orientation/{$orientation->id}", ['name' => 'Etat Civil Rénové'])
            ->assertRedirect(route('orientation.index'));
        $this->assertSame('Etat Civil Rénové', $orientation->fresh()->name);

        $this->actingAs($admin)
            ->delete("/orientation/{$orientation->id}")
            ->assertRedirect(route('orientation.index'));
        $this->assertDatabaseMissing('orientation', ['id' => $orientation->id]);
    }

    public function test_admin_can_create_a_type_de_demande(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post('/typedem', ['name' => 'Certificat de résidence'])
            ->assertRedirect(route('typedem.index'));

        $this->assertDatabaseHas('typedem', ['name' => 'Certificat de résidence']);
    }

    public function test_non_admin_cannot_create_a_type_de_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)
            ->post('/typedem', ['name' => 'Certificat de résidence'])
            ->assertForbidden();

        $this->assertDatabaseCount('typedem', 0);
    }

    public function test_typedem_name_is_required(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post('/typedem', ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_cannot_create_a_duplicate_type_de_demande(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Typedem::create(['name' => 'Autorisation']);

        $this->actingAs($admin)
            ->post('/typedem', ['name' => 'Autorisation'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('typedem', 1);
    }

    public function test_cannot_rename_a_type_de_demande_to_an_existing_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Typedem::create(['name' => 'Autorisation']);
        $reclamation = Typedem::create(['name' => 'Reclamation']);

        $this->actingAs($admin)
            ->put("/typedem/{$reclamation->id}", ['name' => 'Autorisation'])
            ->assertSessionHasErrors('name');

        $this->assertSame('Reclamation', $reclamation->fresh()->name);
    }

    public function test_can_rename_a_type_de_demande_to_its_own_unchanged_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $typedem = Typedem::create(['name' => 'Autorisation']);

        $this->actingAs($admin)
            ->put("/typedem/{$typedem->id}", ['name' => 'Autorisation'])
            ->assertRedirect(route('typedem.index'));
    }

    public function test_typedem_index_shows_an_empty_state_when_search_matches_nothing(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Typedem::create(['name' => 'Certificat de résidence']);

        $response = $this->actingAs($admin)->get('/typedem?search=Introuvable');

        $response->assertOk();
        $response->assertSee('Aucun type de demande ne correspond');
        $response->assertDontSee('Certificat de résidence');
    }

    public function test_typedem_index_search_filters_by_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Typedem::create(['name' => 'Certificat de résidence']);
        Typedem::create(['name' => 'Passeport']);

        $response = $this->actingAs($admin)->get('/typedem?search=Certificat');

        $response->assertSee('Certificat de résidence');
        $response->assertDontSee('Passeport');
    }
}
