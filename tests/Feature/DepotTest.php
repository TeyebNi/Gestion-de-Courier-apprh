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

    public function test_destroy_soft_deletes_the_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);

        $response = $this->actingAs($user)->delete("/depot/{$demande->id}");

        $response->assertRedirect(route('depot.index'));
        // Soft delete : la ligne reste en base (récupérable) mais disparaît des listes normales.
        $this->assertSoftDeleted('tabdepot', ['id' => $demande->id]);
        $this->assertDatabaseHas('tabdepot', ['id' => $demande->id]);
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

    public function test_index_lists_the_most_recently_added_demande_first(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $older = Tabdepot::create(['nom' => 'Ahmed Ould Sidi', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);
        $newer = Tabdepot::create(['nom' => 'Fatimetou Mint Ely', 'tel' => '22334456', 'daterecp' => now()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertViewHas('tabdepot', function ($tabdepot) use ($older, $newer) {
            return $tabdepot->first()->id === $newer->id
                && $tabdepot->last()->id === $older->id;
        });
    }

    public function test_cabinet_maire_and_service_users_cannot_access_depot(): void
    {
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);

        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);
        $maire = User::factory()->create(['role' => UserRole::Maire]);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        foreach ([$cabinet, $maire, $serviceUser] as $user) {
            $this->actingAs($user)->get('/depot')->assertForbidden();
            $this->actingAs($user)->post('/depot', ['nom' => 'X', 'tel' => '22222222'])->assertForbidden();
            $this->actingAs($user)->put("/depot/{$demande->id}", ['nom' => 'Ahmed', 'tel' => '22334455'])->assertForbidden();
            $this->actingAs($user)->delete("/depot/{$demande->id}")->assertForbidden();
        }

        $this->assertDatabaseHas('tabdepot', ['id' => $demande->id]);
    }

    public function test_accueil_and_admin_can_access_depot(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User, 'service' => null]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($accueil)->get('/depot')->assertOk();
        $this->actingAs($admin)->get('/depot')->assertOk();
    }

    public function test_store_saves_objet_and_reference(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'nom' => 'Ahmed Ould Sidi',
            'tel' => '22334455',
            'objet' => 'Demande de raccordement eau',
            'reference' => 'MI/2026/245',
        ])->assertRedirect(route('depot.index'));

        $demande = Tabdepot::firstOrFail();
        $this->assertSame('Demande de raccordement eau', $demande->objet);
        $this->assertSame('MI/2026/245', $demande->reference);
    }

    public function test_institution_sender_does_not_require_a_phone_number(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => "Ministère de l'Intérieur",
            'piece_jointe' => \Illuminate\Http\UploadedFile::fake()->create('lettre.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionDoesntHaveErrors('tel');
        $this->assertDatabaseCount('tabdepot', 1);
        $this->assertNull(Tabdepot::first()->tel);
        $this->assertSame("Ministère de l'Intérieur", Tabdepot::first()->origine_detail);
    }

    public function test_institution_sender_requires_its_name(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'piece_jointe' => \Illuminate\Http\UploadedFile::fake()->create('lettre.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('origine_detail');
        $this->assertDatabaseCount('tabdepot', 0);
    }

    public function test_index_shows_institution_name_instead_of_placeholder_text(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => "Ministère de l'Intérieur",
            'daterecp' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertOk();
        $response->assertSee("Ministère de l'Intérieur");
        $response->assertDontSee('(institution)');
    }

    public function test_internal_demande_never_stores_nni_or_adresse(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'nom' => 'Ahmed Ould Sidi',
            'tel' => '22334455',
            'origine' => 'interne',
            'typdm' => 'Note de service',
            // NNI/adresse ne devraient jamais être enregistrés pour une demande interne,
            // même si un client contournant le JS les envoie quand même.
            'nni' => '1234567890',
            'adresse' => 'Nouakchott',
        ]);

        $demande = Tabdepot::firstOrFail();
        $this->assertNull($demande->nni);
        $this->assertNull($demande->adresse);
    }

    public function test_index_search_matches_objet(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d'), 'objet' => 'Raccordement eau']);
        Tabdepot::create(['nom' => 'Fatimetou', 'tel' => '22334456', 'daterecp' => now()->format('Y-m-d'), 'objet' => 'Certificat de résidence']);

        $response = $this->actingAs($user)->get('/depot?search=Raccordement');

        $response->assertOk();
        $response->assertSee('Ahmed');
        $response->assertDontSee('Fatimetou');
    }

    public function test_cannot_delete_a_demande_once_it_left_accueil(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create([
            'nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d'),
            'statut_circuit' => 'fatou',
        ]);

        $this->actingAs($user)->delete("/depot/{$demande->id}")->assertForbidden();

        $this->assertDatabaseHas('tabdepot', ['id' => $demande->id, 'deleted_at' => null]);
    }

    public function test_can_still_delete_a_demande_still_at_accueil(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);

        $this->actingAs($user)->delete("/depot/{$demande->id}")->assertRedirect(route('depot.index'));

        $this->assertSoftDeleted('tabdepot', ['id' => $demande->id]);
    }

    public function test_update_logs_an_history_entry(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);

        $this->actingAs($user)->put("/depot/{$demande->id}", [
            'nom' => 'Ahmed Modifié',
            'tel' => '22334455',
        ]);

        $this->assertSame(1, $demande->historiques()->count());
    }

    public function test_sidebar_shows_count_of_demandes_not_yet_sent_to_cabinet(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);
        Tabdepot::create(['nom' => 'Fatimetou', 'tel' => '22334456', 'daterecp' => now()->format('Y-m-d')]);
        Tabdepot::create(['nom' => 'Deja envoye', 'tel' => '22334457', 'daterecp' => now()->format('Y-m-d'), 'statut_circuit' => 'fatou']);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertOk();
        $response->assertSee('title="En attente d\'envoi au Cabinet"', false);
        $response->assertSee('>2<', false);
    }

    public function test_trashed_demandes_can_be_restored(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);
        $demande->delete();

        $listResponse = $this->actingAs($user)->get('/depot-corbeille');
        $listResponse->assertOk();
        $listResponse->assertSee('Ahmed');

        $this->actingAs($user)->post("/depot-corbeille/{$demande->id}/restaurer")
            ->assertRedirect(route('depot.trashed'));

        $this->assertDatabaseHas('tabdepot', ['id' => $demande->id, 'deleted_at' => null]);
    }

    public function test_non_admin_cannot_permanently_delete_a_trashed_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);
        $demande->delete();

        $this->actingAs($user)->delete("/depot-corbeille/{$demande->id}")->assertForbidden();

        $this->assertSoftDeleted('tabdepot', ['id' => $demande->id]);
    }

    public function test_admin_can_permanently_delete_a_trashed_demande(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $demande = Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22334455', 'daterecp' => now()->format('Y-m-d')]);
        $demande->delete();

        $this->actingAs($admin)->delete("/depot-corbeille/{$demande->id}")
            ->assertRedirect(route('depot.trashed'));

        $this->assertDatabaseMissing('tabdepot', ['id' => $demande->id]);
    }
}
