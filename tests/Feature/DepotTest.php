<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DepotTest extends TestCase
{
    use RefreshDatabase;

    private function makeDepot(array $overrides = []): Tabdepot
    {
        return Tabdepot::create(array_merge([
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
            'daterecp' => now()->format('Y-m-d'),
        ], $overrides));
    }

    public function test_store_creates_a_demande_and_defaults_the_reception_date(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $response->assertRedirect(route('depot.index'));

        $demande = Tabdepot::firstOrFail();
        $this->assertSame('MI/2026/245', $demande->reference);
        $this->assertSame(now()->format('Y-m-d'), $demande->daterecp);
    }

    public function test_store_logs_who_registered_the_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'name' => 'Fatimetou Accueil']);

        $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $demande = Tabdepot::firstOrFail();
        $historique = $demande->historiques()->first();

        $this->assertNotNull($historique);
        $this->assertSame('accueil', $historique->vers_statut);
        $this->assertSame($user->id, $historique->user_id);
    }

    public function test_store_requires_a_code(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
        ]);

        $response->assertSessionHasErrors('reference');
        $this->assertDatabaseCount('tabdepot', 0);
    }

    public function test_store_requires_an_origine(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'reference' => 'MI/2026/245',
        ]);

        $response->assertSessionHasErrors('origine');
        $this->assertDatabaseCount('tabdepot', 0);
    }

    public function test_store_rejects_a_code_already_used_by_another_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'MI/2026/245']);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $response->assertSessionHasErrors('reference');
        $this->assertDatabaseCount('tabdepot', 1);
    }

    public function test_store_trims_the_code_before_saving_and_checking_uniqueness(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'MI/2026/245']);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => '  MI/2026/245  ',
        ]);

        $response->assertSessionHasErrors('reference');
        $this->assertDatabaseCount('tabdepot', 1);
    }

    public function test_store_saves_the_code_without_surrounding_whitespace(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => '  MI/2026/999  ',
        ]);

        $demande = Tabdepot::firstOrFail();
        $this->assertSame('MI/2026/999', $demande->reference);
    }

    public function test_store_rejects_a_code_already_used_by_a_trashed_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $trashed = $this->makeDepot(['reference' => 'MI/2026/245']);
        $trashed->delete();

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $response->assertSessionHasErrors('reference');
    }

    public function test_update_rejects_a_code_already_used_by_another_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'MI/2026/245']);
        $other = $this->makeDepot(['reference' => 'MI/2026/999']);

        $response = $this->actingAs($user)->put("/depot/{$other->id}", [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $response->assertSessionHasErrors('reference');
        $this->assertSame('MI/2026/999', $other->fresh()->reference);
    }

    public function test_update_keeping_the_same_code_is_allowed(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot(['reference' => 'MI/2026/245']);

        $response = $this->actingAs($user)->put("/depot/{$demande->id}", [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
            'objet' => 'Objet mis à jour',
        ]);

        $response->assertSessionDoesntHaveErrors('reference');
        $this->assertSame('Objet mis à jour', $demande->fresh()->objet);
    }

    public function test_store_rejects_an_unknown_origine_value(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'reference' => 'MI/2026/245',
            'origine' => 'autre',
        ]);

        $response->assertSessionHasErrors('origine');
        $this->assertDatabaseCount('tabdepot', 0);
    }

    public function test_store_accepts_an_internal_demande_without_any_service_detail(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'reference' => 'NOTE-1',
            'origine' => 'interne',
        ])->assertRedirect(route('depot.index'));

        $demande = Tabdepot::firstOrFail();
        $this->assertSame('interne', $demande->origine);
        $this->assertNull($demande->origine_detail);
    }

    public function test_store_ignores_a_service_detail_sent_for_an_internal_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'reference' => 'NOTE-1',
            'origine' => 'interne',
            'origine_detail' => 'Etat Civil',
        ]);

        $demande = Tabdepot::firstOrFail();
        $this->assertNull($demande->origine_detail);
    }

    public function test_store_ignores_a_service_detail_sent_for_an_external_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'reference' => 'MI/2026/245',
            'origine' => 'externe',
            'origine_detail' => 'Ceci ne devrait jamais être enregistré',
        ]);

        $demande = Tabdepot::firstOrFail();
        $this->assertNull($demande->origine_detail);
    }

    public function test_a_duplicate_code_error_reopens_the_create_modal_with_the_message_inline(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'MI/2026/245']);

        $response = $this->actingAs($user)->from('/depot')->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
            'objet' => 'Second envoi',
        ]);

        $response->assertRedirect('/depot');
        $followUp = $this->actingAs($user)->get('/depot');

        $followUp->assertSee('Ce code est déjà utilisé par une autre demande.');
        $followUp->assertSee("$('#exampleModal').modal('show');", false);
    }

    public function test_a_duplicate_code_error_reopens_the_edit_modal_for_the_right_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'MI/2026/245']);
        $other = $this->makeDepot(['reference' => 'MI/2026/999']);

        $this->actingAs($user)->from('/depot')->put("/depot/{$other->id}", [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
            'form_source' => 'edit',
            'depot_id' => $other->id,
        ]);

        $followUp = $this->actingAs($user)->get('/depot');

        $followUp->assertSee('Ce code est déjà utilisé par une autre demande.');
        $followUp->assertSee(json_encode((string) $other->id), false);
        $followUp->assertSee("$('#exampleModal-edit').modal('show');", false);
    }

    public function test_store_saves_objet(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
            'objet' => 'Demande de raccordement eau',
        ])->assertRedirect(route('depot.index'));

        $demande = Tabdepot::firstOrFail();
        $this->assertSame('Demande de raccordement eau', $demande->objet);
    }

    public function test_store_objet_is_optional(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $response->assertSessionDoesntHaveErrors('objet');
        $this->assertDatabaseCount('tabdepot', 1);
    }

    public function test_update_changes_the_code_and_objet(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();

        $response = $this->actingAs($user)->put("/depot/{$demande->id}", [
            'origine' => 'externe',
            'reference' => 'MI/2026/999',
            'objet' => 'Nouvel objet',
        ]);

        $response->assertRedirect(route('depot.index'));
        $demande->refresh();
        $this->assertSame('MI/2026/999', $demande->reference);
        $this->assertSame('Nouvel objet', $demande->objet);
    }

    public function test_destroy_soft_deletes_the_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();

        $response = $this->actingAs($user)->delete("/depot/{$demande->id}");

        $response->assertRedirect(route('depot.index'));
        // Soft delete : la ligne reste en base (récupérable) mais disparaît des listes normales.
        $this->assertSoftDeleted('tabdepot', ['id' => $demande->id]);
        $this->assertDatabaseHas('tabdepot', ['id' => $demande->id]);
    }

    public function test_index_search_filters_by_code(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'MI/2026/245']);
        $this->makeDepot(['reference' => 'AUTRE/2026/999']);

        $response = $this->actingAs($user)->get('/depot?search=MI/2026/245');

        $response->assertOk();
        $response->assertSee('MI/2026/245');
        $response->assertDontSee('AUTRE/2026/999');
    }

    public function test_index_search_matches_objet(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'CODE-1', 'objet' => 'Raccordement eau']);
        $this->makeDepot(['reference' => 'CODE-2', 'objet' => 'Certificat de résidence']);

        $response = $this->actingAs($user)->get('/depot?search=Raccordement');

        $response->assertOk();
        $response->assertSee('CODE-1');
        $response->assertDontSee('CODE-2');
    }

    public function test_index_lists_the_most_recently_added_demande_first(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $older = $this->makeDepot(['reference' => 'CODE-1']);
        $newer = $this->makeDepot(['reference' => 'CODE-2']);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertViewHas('tabdepot', function ($tabdepot) use ($older, $newer) {
            return $tabdepot->first()->id === $newer->id
                && $tabdepot->last()->id === $older->id;
        });
    }

    public function test_cabinet_and_service_users_cannot_access_depot(): void
    {
        $demande = $this->makeDepot();

        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        foreach ([$cabinet, $serviceUser] as $user) {
            $this->actingAs($user)->get('/depot')->assertForbidden();
            $this->actingAs($user)->post('/depot', ['origine' => 'externe', 'reference' => 'X'])->assertForbidden();
            $this->actingAs($user)->put("/depot/{$demande->id}", ['origine' => 'externe', 'reference' => 'X'])->assertForbidden();
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

    public function test_cannot_delete_a_demande_once_it_left_accueil(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot(['statut_circuit' => 'fatou']);

        $this->actingAs($user)->delete("/depot/{$demande->id}")->assertForbidden();

        $this->assertDatabaseHas('tabdepot', ['id' => $demande->id, 'deleted_at' => null]);
    }

    public function test_cannot_edit_a_demande_once_it_left_accueil(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot(['statut_circuit' => 'fatou']);

        $this->actingAs($user)->put("/depot/{$demande->id}", [
            'origine' => 'externe',
            'reference' => 'MI/2026/999',
        ])->assertForbidden();

        $this->assertSame($demande->reference, $demande->fresh()->reference);
    }

    public function test_the_modifier_button_only_shows_for_demandes_still_at_accueil(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'A-ACCUEIL']);
        $this->makeDepot(['reference' => 'B-PARTIE', 'statut_circuit' => 'fatou']);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertOk();
        // Une seule des deux demandes est encore à l'accueil : un seul bouton Modifier attendu.
        $this->assertSame(1, substr_count($response->getContent(), 'data-target="#exampleModal-edit"'));
    }

    public function test_can_still_delete_a_demande_still_at_accueil(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();

        $this->actingAs($user)->delete("/depot/{$demande->id}")->assertRedirect(route('depot.index'));

        $this->assertSoftDeleted('tabdepot', ['id' => $demande->id]);
    }

    public function test_update_logs_an_history_entry(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();

        $this->actingAs($user)->put("/depot/{$demande->id}", [
            'origine' => 'externe',
            'reference' => 'MI/2026/999',
        ]);

        $this->assertSame(1, $demande->historiques()->count());
    }

    public function test_sidebar_shows_count_of_demandes_not_yet_sent_to_cabinet(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot(['reference' => 'CODE-1']);
        $this->makeDepot(['reference' => 'CODE-2']);
        $this->makeDepot(['reference' => 'CODE-3', 'statut_circuit' => 'fatou']);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertOk();
        $response->assertSee('title="En attente d\'envoi au Cabinet"', false);
        $response->assertSee('>2<', false);
    }

    public function test_trashed_demandes_can_be_restored(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot(['reference' => 'CODE-A-RESTAURER']);
        $demande->delete();

        $listResponse = $this->actingAs($user)->get('/depot-corbeille');
        $listResponse->assertOk();
        $listResponse->assertSee('CODE-A-RESTAURER');

        $this->actingAs($user)->post("/depot-corbeille/{$demande->id}/restaurer")
            ->assertRedirect(route('depot.trashed'));

        $this->assertDatabaseHas('tabdepot', ['id' => $demande->id, 'deleted_at' => null]);
    }

    public function test_trashed_search_filters_by_code_or_objet(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $a = $this->makeDepot(['reference' => 'CODE-A']);
        $a->delete();
        $b = $this->makeDepot(['reference' => 'CODE-B']);
        $b->delete();

        $response = $this->actingAs($user)->get('/depot-corbeille?search=CODE-A');

        $response->assertOk();
        $response->assertSee('CODE-A');
        $response->assertDontSee('CODE-B');
    }

    public function test_trashed_shows_an_empty_state_when_search_matches_nothing(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();
        $demande->delete();

        $response = $this->actingAs($user)->get('/depot-corbeille?search=Introuvable');

        $response->assertOk();
        $response->assertSee('Aucune demande supprimée ne correspond');
    }

    public function test_non_admin_cannot_permanently_delete_a_trashed_demande(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();
        $demande->delete();

        $this->actingAs($user)->delete("/depot-corbeille/{$demande->id}")->assertForbidden();

        $this->assertSoftDeleted('tabdepot', ['id' => $demande->id]);
    }

    public function test_admin_can_permanently_delete_a_trashed_demande(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $demande = $this->makeDepot();
        $demande->delete();

        $this->actingAs($admin)->delete("/depot-corbeille/{$demande->id}")
            ->assertRedirect(route('depot.trashed'));

        $this->assertDatabaseMissing('tabdepot', ['id' => $demande->id]);
    }

    public function test_index_page_shows_the_attachment_field_in_the_create_and_edit_modals(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertOk();
        $response->assertSee('name="piece_jointe"', false);
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_index_list_shows_a_link_to_the_attachment_when_one_exists(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::User]);
        $path = UploadedFile::fake()->image('scan.jpg')->store('pieces-jointes', 'public');
        $this->makeDepot(['reference' => 'AVEC-SCAN', 'piece_jointe' => $path]);
        $this->makeDepot(['reference' => 'SANS-SCAN']);

        $response = $this->actingAs($user)->get('/depot');

        $response->assertOk();
        $response->assertSee('title="Voir la pièce jointe"', false);
        $response->assertSee(asset('storage/' . $path), false);
    }

    public function test_store_saves_the_scanned_attachment_as_a_path_not_the_file_itself(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::User]);
        $file = UploadedFile::fake()->image('scan.jpg');

        $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
            'piece_jointe' => $file,
        ])->assertRedirect(route('depot.index'));

        $demande = Tabdepot::firstOrFail();
        $this->assertIsString($demande->piece_jointe);
        Storage::disk('public')->assertExists($demande->piece_jointe);
    }

    public function test_store_rejects_an_attachment_of_an_unsupported_type(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::User]);
        $file = UploadedFile::fake()->create('malware.exe', 10);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
            'piece_jointe' => $file,
        ]);

        $response->assertSessionHasErrors('piece_jointe');
        $this->assertDatabaseCount('tabdepot', 0);
    }

    public function test_store_without_an_attachment_leaves_piece_jointe_null(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $this->assertNull(Tabdepot::firstOrFail()->piece_jointe);
    }

    public function test_update_can_attach_a_scan_that_was_missing_at_intake(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();
        $file = UploadedFile::fake()->image('scan.png');

        $this->actingAs($user)->put("/depot/{$demande->id}", [
            'origine' => 'externe',
            'reference' => $demande->reference,
            'piece_jointe' => $file,
        ]);

        $demande->refresh();
        $this->assertIsString($demande->piece_jointe);
        Storage::disk('public')->assertExists($demande->piece_jointe);
    }

    public function test_update_replacing_the_attachment_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::User]);
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('pieces-jointes', 'public');
        $demande = $this->makeDepot(['piece_jointe' => $oldPath]);
        $newFile = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($user)->put("/depot/{$demande->id}", [
            'origine' => 'externe',
            'reference' => $demande->reference,
            'piece_jointe' => $newFile,
        ]);

        $demande->refresh();
        $this->assertNotSame($oldPath, $demande->piece_jointe);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($demande->piece_jointe);
    }

    public function test_update_without_a_new_file_keeps_the_existing_attachment(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::User]);
        $existingPath = UploadedFile::fake()->image('scan.jpg')->store('pieces-jointes', 'public');
        $demande = $this->makeDepot(['piece_jointe' => $existingPath]);

        $this->actingAs($user)->put("/depot/{$demande->id}", [
            'origine' => 'externe',
            'reference' => $demande->reference,
            'objet' => 'Objet mis à jour',
        ]);

        $this->assertSame($existingPath, $demande->fresh()->piece_jointe);
        Storage::disk('public')->assertExists($existingPath);
    }

    public function test_force_deleting_a_demande_removes_its_attachment_from_disk(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $path = UploadedFile::fake()->image('scan.jpg')->store('pieces-jointes', 'public');
        $demande = $this->makeDepot(['piece_jointe' => $path]);
        $demande->delete();

        $this->actingAs($admin)->delete("/depot-corbeille/{$demande->id}");

        Storage::disk('public')->assertMissing($path);
    }

    public function test_store_shows_a_clean_success_message(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->post('/depot', [
            'origine' => 'externe',
            'reference' => 'MI/2026/245',
        ]);

        $response->assertSessionHas('success', 'Demande MI/2026/245 enregistrée avec succès.');
        $response->assertSessionMissing('succes');
    }

    public function test_index_shows_an_empty_state_when_search_matches_nothing(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->makeDepot();

        $response = $this->actingAs($user)->get('/depot?search=Introuvable');

        $response->assertOk();
        $response->assertSee('Aucune demande ne correspond');
    }

    public function test_receipt_shows_code_objet_origine_and_a_localized_date(): void
    {
        $demande = $this->makeDepot([
            'daterecp' => '2026-08-30',
            'objet' => 'Raccordement eau',
            'reference' => 'MI/2026/245',
        ]);

        $html = view('depot.print_reçu', ['detailf' => $demande])->render();

        $this->assertStringContainsString('Raccordement eau', $html);
        $this->assertStringContainsString('MI/2026/245', $html);
        $this->assertStringContainsString('30/08/2026', $html);
        $this->assertStringNotContainsString('2026-08-30', $html);
    }

    public function test_daterecp_formatted_does_not_crash_on_malformed_legacy_data(): void
    {
        $demande = $this->makeDepot(['daterecp' => 'valeur-invalide']);

        $this->assertSame('valeur-invalide', $demande->daterecpFormatted());
    }

    public function test_accueil_can_print_a_receipt(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $demande = $this->makeDepot();

        $this->actingAs($user)->get("/depot/print_re%C3%A7u/{$demande->id}")->assertOk();
    }

    public function test_a_service_user_cannot_print_a_receipt_for_a_citizens_demande(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $demande = $this->makeDepot();

        $this->actingAs($serviceUser)->get("/depot/print_re%C3%A7u/{$demande->id}")->assertForbidden();
    }

    public function test_cabinet_can_print_a_receipt_to_carry_to_the_maire(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);
        $demande = $this->makeDepot();

        $this->actingAs($cabinet)->get("/depot/print_re%C3%A7u/{$demande->id}")->assertOk();
    }
}
