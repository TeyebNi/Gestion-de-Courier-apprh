<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Orientation;
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

    public function test_non_admin_cannot_even_view_the_orientation_page(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->get('/orientation')->assertForbidden();
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

    public function test_cannot_create_a_duplicate_orientation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Orientation::create(['name' => 'Etat Civil']);

        $this->actingAs($admin)
            ->post('/orientation', ['name' => 'Etat Civil'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('orientation', 1);
    }

    public function test_cannot_rename_an_orientation_to_an_existing_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Orientation::create(['name' => 'Etat Civil']);
        $urbanisme = Orientation::create(['name' => 'Urbanisme']);

        $this->actingAs($admin)
            ->put("/orientation/{$urbanisme->id}", ['name' => 'Etat Civil'])
            ->assertSessionHasErrors('name');

        $this->assertSame('Urbanisme', $urbanisme->fresh()->name);
    }

    public function test_can_rename_an_orientation_to_its_own_unchanged_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $orientation = Orientation::create(['name' => 'Etat Civil']);

        $this->actingAs($admin)
            ->put("/orientation/{$orientation->id}", ['name' => 'Etat Civil'])
            ->assertRedirect(route('orientation.index'));
    }

    public function test_non_admin_cannot_update_delete_or_export_an_orientation(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $orientation = Orientation::create(['name' => 'Etat Civil']);

        $this->actingAs($user)->put("/orientation/{$orientation->id}", ['name' => 'Autre'])->assertForbidden();
        $this->actingAs($user)->delete("/orientation/{$orientation->id}")->assertForbidden();
        $this->actingAs($user)->get('/orientation/export')->assertForbidden();

        $this->assertSame('Etat Civil', $orientation->fresh()->name);
    }

    public function test_orientation_export_returns_a_csv_of_all_orientations(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Orientation::create(['name' => 'Etat Civil']);
        Orientation::create(['name' => 'Urbanisme']);

        $response = $this->actingAs($admin)->get('/orientation/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Etat Civil', $csv);
        $this->assertStringContainsString('Urbanisme', $csv);
    }

    public function test_orientation_index_shows_an_empty_state_when_search_matches_nothing(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Orientation::create(['name' => 'Etat Civil']);

        $response = $this->actingAs($admin)->get('/orientation?search=Introuvable');

        $response->assertOk();
        $response->assertSee('Aucune orientation ne correspond');
        $response->assertDontSee('Etat Civil');
    }

    public function test_orientation_index_search_filters_by_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Orientation::create(['name' => 'Etat Civil']);
        Orientation::create(['name' => 'Douanes']);

        $response = $this->actingAs($admin)->get('/orientation?search=Etat');

        $response->assertSee('Etat Civil');
        $response->assertDontSee('Douanes');
    }

}
