<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepotTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_a_demande_and_defaults_the_reception_date(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'nom' => 'Ahmed Ould Sidi',
            'tel' => '22334455',
            // daterecp volontairement omis : doit être défaulté au lieu de planter en SQL.
        ]);

        $response->assertRedirect(route('depot.index'));

        $demande = Tabdepot::firstOrFail();
        $this->assertSame('Ahmed Ould Sidi', $demande->nom);
        $this->assertSame(now()->format('Y-m-d'), $demande->daterecp);
    }

    public function test_store_requires_a_phone_number(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'nom' => 'Ahmed Ould Sidi',
        ]);

        $response->assertSessionHasErrors('tel');
        $this->assertDatabaseCount('tabdepot', 0);
    }

    public function test_store_requires_typdm_for_an_internal_request(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'nom' => 'Ahmed Ould Sidi',
            'tel' => '22334455',
            'origine' => 'interne',
        ]);

        $response->assertSessionHasErrors('typdm');
    }

    public function test_update_rejects_a_phone_number_that_is_not_eight_digits(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);

        $response = $this->actingAs($user)->put("/depot/{$demande->id}", [
            'nom' => 'Ahmed',
            'tel' => '123',
        ]);

        $response->assertSessionHasErrors('tel');
        $this->assertSame('22334455', $demande->fresh()->tel);
    }

    public function test_destroy_removes_the_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);

        $response = $this->actingAs($user)->delete("/depot/{$demande->id}");

        $response->assertRedirect(route('depot.index'));
        $this->assertDatabaseMissing('tabdepot', ['id' => $demande->id]);
    }

    public function test_index_search_filters_by_name(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        Tabdepot::create(['nom' => 'Ahmed Ould Sidi', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);
        Tabdepot::create(['nom' => 'Fatimetou Mint Ely', 'tel' => '22334456', 'daterecp' => now()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get('/depot?search=Fatimetou');

        $response->assertOk();
        $response->assertSee('Fatimetou Mint Ely');
        $response->assertDontSee('Ahmed Ould Sidi');
    }
}
