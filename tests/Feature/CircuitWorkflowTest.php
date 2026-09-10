<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DemandeHistorique;
use App\Models\Orientation;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CircuitWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeDepot(): Tabdepot
    {
        return Tabdepot::create([
            'nom' => 'Citoyen Test',
            'tel' => '22222222',
            'daterecp' => now()->format('Y-m-d'),
        ]);
    }

    public function test_new_depot_starts_at_accueil(): void
    {
        $depot = $this->makeDepot();

        $this->assertSame('accueil', $depot->fresh()->statut_circuit);
    }

    public function test_historique_view_shows_cabinet_instead_of_the_internal_fatou_label(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();

        $this->actingAs($accueil)->post("/circuit/{$depot->id}/envoyer-fatou");

        $response = $this->actingAs($accueil)->get("/circuit/{$depot->id}/historique");

        $response->assertOk();
        $response->assertSee('Cabinet');
        $response->assertDontSee('Fatou');
    }

    public function test_historique_view_shows_full_demande_details(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = Tabdepot::create([
            'origine' => 'interne',
            'origine_detail' => 'Etat Civil',
            'daterecp' => now()->format('Y-m-d'),
            'objet' => 'Raccordement eau',
            'reference' => 'MI/2026/245',
            'service_assigne' => 'Etat Civil',
        ]);

        $response = $this->actingAs($accueil)->get("/circuit/{$depot->id}/historique");

        $response->assertOk();
        $response->assertSee('Raccordement eau');
        $response->assertSee('MI/2026/245');
        $response->assertSee('Etat Civil');
    }

    public function test_fatou_index_shows_institution_name_objet_and_a_localized_date(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => "Ministère de l'Intérieur",
            'objet' => 'Demande de raccordement eau',
            'daterecp' => '2026-08-30',
            'statut_circuit' => 'fatou',
        ]);

        $response = $this->actingAs($fatou)->get('/circuit/fatou');

        $response->assertOk();
        $response->assertSee("Ministère de l&#039;Intérieur", false);
        $response->assertSee('Demande de raccordement eau');
        $response->assertSee('30/08/2026');
        $response->assertDontSee('2026-08-30');
    }

    public function test_service_index_shows_institution_name_objet_and_a_localized_date_and_time(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $pending = Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => "Ministère de l'Intérieur",
            'objet' => 'Demande de raccordement eau',
            'daterecp' => '2026-08-01',
            'statut_circuit' => 'service',
            'service_assigne' => 'Etat Civil',
        ]);
        $pending->timestamps = false;
        $pending->updated_at = '2026-08-30 14:05:00';
        $pending->save();

        $treated = Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => 'Ministère des Finances',
            'objet' => 'Demande de subvention',
            'daterecp' => '2026-08-01',
            'statut_circuit' => 'cloture',
            'service_assigne' => 'Etat Civil',
        ]);
        $treated->timestamps = false;
        $treated->updated_at = '2026-08-15 09:30:00';
        $treated->save();

        $response = $this->actingAs($serviceUser)->get('/circuit/service');

        // La colonne Date affiche désormais quand l'action a eu lieu (affectation/
        // clôture, avec l'heure), pas la date de réception d'origine.
        $response->assertOk();
        $response->assertSee("Ministère de l&#039;Intérieur", false);
        $response->assertSee('Demande de raccordement eau');
        $response->assertSee('30/08/2026 14:05');
        $response->assertSee('Ministère des Finances');
        $response->assertSee('Demande de subvention');
        $response->assertSee('15/08/2026 09:30');
        $response->assertDontSee('2026-08-30');
        $response->assertDontSee('2026-08-15');
    }

    public function test_suivi_shows_the_maires_remark(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update([
            'statut_circuit' => 'service',
            'decision_maire' => 'accepte',
            'remarque_maire' => 'Dossier prioritaire, à traiter rapidement.',
        ]);

        $response = $this->actingAs($accueil)->get('/circuit/suivi');

        $response->assertOk();
        $response->assertSee('Dossier prioritaire, à traiter rapidement.');
    }

    public function test_fatou_form_preselects_service_for_an_internal_demande(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        Orientation::create(['name' => 'Informatique']);
        Orientation::create(['name' => 'Etat Civil']);
        Tabdepot::create([
            'nom' => 'Agent Test',
            'tel' => '22222222',
            'daterecp' => now()->format('Y-m-d'),
            'origine' => 'interne',
            'origine_detail' => 'Informatique',
            'statut_circuit' => 'fatou',
        ]);

        $response = $this->actingAs($fatou)->get('/circuit/fatou');

        // Le sous-menu "Quel service ?" est peuplé côté client (JS) à partir
        // de la liste des services : seul l'attribut qui pilote cette
        // pré-sélection est vérifiable côté serveur.
        $response->assertOk();
        $response->assertSee('data-preselected="Informatique"', false);
        $response->assertSee('<option value="service" selected>Service</option>', false);
    }

    public function test_historique_transferts_are_paginated_by_five(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();

        foreach (range(1, 15) as $i) {
            DemandeHistorique::create([
                'tabdepot_id' => $depot->id,
                'vers_statut' => 'fatou',
                'commentaire' => "Entrée numéro {$i}",
            ]);
        }

        $response = $this->actingAs($accueil)->get("/circuit/{$depot->id}/historique");

        $response->assertOk();
        $response->assertViewHas('historiques', function ($historiques) {
            return $historiques->count() === 5 && $historiques->total() === 15;
        });
    }

    public function test_full_circuit_accueil_to_fatou_to_service(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        Orientation::create(['name' => 'Etat Civil']);

        $depot = $this->makeDepot();

        // Accueil -> Cabinet de Maire
        $this->actingAs($accueil)
            ->post("/circuit/{$depot->id}/envoyer-fatou")
            ->assertRedirect();
        $this->assertSame('fatou', $depot->fresh()->statut_circuit);

        // Le Cabinet saisit les annotations du Maire (recueillies à la main)
        // et transmet directement au service concerné, en une seule étape.
        $this->actingAs($fatou)
            ->post("/circuit/{$depot->id}/decider", [
                'remarque_maire' => 'Vu par le Maire, à traiter en urgence.',
                'destination_category' => 'service',
                'destination_value' => 'Etat Civil',
            ])
            ->assertRedirect();

        $depot->refresh();
        $this->assertSame('service', $depot->statut_circuit);
        $this->assertSame('Vu par le Maire, à traiter en urgence.', $depot->remarque_maire);
        $this->assertSame('Etat Civil', $depot->service_assigne);

        // Le service clôture la demande : fin du circuit.
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $this->actingAs($serviceUser)
            ->post("/circuit/{$depot->id}/cloturer", ['resolution' => 'traiter'])
            ->assertRedirect();

        $depot->refresh();
        $this->assertSame('cloture', $depot->statut_circuit);
        $this->assertSame('traiter', $depot->resolution_service);

        // Every transition must be logged in the history trail.
        $this->assertGreaterThanOrEqual(3, $depot->historiques()->count());
    }

    public function test_decide_notifies_the_assigned_service(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        Orientation::create(['name' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'service',
            'destination_value' => 'Etat Civil',
        ]);

        $this->assertDatabaseHas('service_notifications', [
            'service' => 'Etat Civil',
            'iddmd' => $depot->id,
            'is_read' => false,
        ]);
    }

    public function test_decide_without_a_service_classes_the_demande_directly(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'Ne concerne aucun service, classée.',
        ])->assertRedirect();

        $depot->refresh();
        $this->assertSame('cloture', $depot->statut_circuit);
        $this->assertNull($depot->service_assigne);
        $this->assertDatabaseMissing('service_notifications', ['iddmd' => $depot->id]);
    }

    public function test_cabinet_can_print_the_depot_receipt_to_carry_to_the_maire(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->get("/depot/print_re%C3%A7u/{$depot->id}")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_closing_a_demande_shows_the_resolution_wherever_the_status_is_displayed(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $accueil = User::factory()->create(['role' => UserRole::User]);

        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Etat Civil', 'remarque_maire' => 'RAS']);

        $this->actingAs($serviceUser)->post("/circuit/{$depot->id}/cloturer", ['resolution' => 'convoquer']);

        // Cabinet de Maire : le tableau "déjà annotées" doit refléter la vraie résolution.
        $cabinetResponse = $this->actingAs($fatou)->get('/circuit/fatou');
        $cabinetResponse->assertSee('Clôturée (Convoquée)');

        // Accueil : Suivi doit refléter la même chose, pas un générique "traitée par le service".
        $suiviResponse = $this->actingAs($accueil)->get('/circuit/suivi');
        $suiviResponse->assertSee('Clôturée (Convoquée)');
    }

    public function test_cabinet_can_route_a_demande_to_a_specific_maire_adjoint(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        User::factory()->create(['role' => UserRole::User, 'role_kind' => 'maire_adjoint', 'name' => 'Ould Mohamed Lagdhaf', 'service' => 'Ould Mohamed Lagdhaf']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'maire_adjoint',
            'destination_value' => 'Ould Mohamed Lagdhaf',
        ])->assertRedirect();

        $depot->refresh();
        $this->assertSame('service', $depot->statut_circuit);
        $this->assertSame('Ould Mohamed Lagdhaf', $depot->service_assigne);
        $this->assertSame('maire_adjoint', $depot->destination_type);
    }

    public function test_choosing_service_as_the_category_requires_picking_an_actual_service(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'service',
            'destination_value' => '',
        ])->assertSessionHasErrors('destination_value');

        $this->assertSame('fatou', $depot->fresh()->statut_circuit);
    }

    public function test_choosing_maire_adjoint_requires_picking_a_real_account(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'maire_adjoint',
            'destination_value' => 'Personne Inventée',
        ])->assertSessionHasErrors('destination_value');

        $this->assertSame('fatou', $depot->fresh()->statut_circuit);
    }

    public function test_a_maire_adjoint_account_sees_and_closes_its_own_demandes_like_a_service(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $adjointUser = User::factory()->create(['role' => UserRole::User, 'role_kind' => 'maire_adjoint', 'name' => 'Ould Mohamed Lagdhaf', 'service' => 'Ould Mohamed Lagdhaf']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'maire_adjoint',
            'destination_value' => 'Ould Mohamed Lagdhaf',
        ]);

        $response = $this->actingAs($adjointUser)->get('/circuit/service');
        $response->assertOk();
        $response->assertViewHas('demandes', fn ($demandes) => $demandes->contains('id', $depot->id));

        $this->actingAs($adjointUser)->post("/circuit/{$depot->id}/cloturer", ['resolution' => 'traiter'])
            ->assertRedirect();

        $this->assertSame('cloture', $depot->fresh()->statut_circuit);
    }

    public function test_a_demande_routed_to_one_maire_adjoint_is_not_visible_to_another(): void
    {
        // Chaque Adjoint au Maire a sa propre file individuelle, comme un
        // service — contrairement à Cabinet, qui lui reste une file commune.
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $adjoint1 = User::factory()->create(['role' => UserRole::User, 'role_kind' => 'maire_adjoint', 'name' => 'Ould Mohamed Lagdhaf', 'service' => 'Ould Mohamed Lagdhaf']);
        $adjoint2 = User::factory()->create(['role' => UserRole::User, 'role_kind' => 'maire_adjoint', 'name' => 'Saleh', 'service' => 'Saleh']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'maire_adjoint',
            'destination_value' => 'Ould Mohamed Lagdhaf',
        ]);

        $this->actingAs($adjoint1)->get('/circuit/service')
            ->assertViewHas('demandes', fn ($demandes) => $demandes->contains('id', $depot->id));

        $this->actingAs($adjoint2)->get('/circuit/service')
            ->assertViewHas('demandes', fn ($demandes) => ! $demandes->contains('id', $depot->id));
    }

    public function test_maire_adjoint_label_appears_wherever_the_status_is_shown(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update([
            'statut_circuit' => 'service',
            'service_assigne' => 'Ould Mohamed Lagdhaf',
            'destination_type' => 'maire_adjoint',
        ]);

        $response = $this->actingAs($accueil)->get('/circuit/suivi');

        $response->assertSee("Chez l&#039;Adjoint au Maire : Ould Mohamed Lagdhaf", false);
    }

    public function test_cabinet_can_route_a_demande_to_a_specific_conseiller(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        User::factory()->create(['role' => UserRole::User, 'role_kind' => 'conseiller', 'name' => 'Zeroug', 'service' => 'Zeroug']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'conseiller',
            'destination_value' => 'Zeroug',
        ])->assertRedirect();

        $depot->refresh();
        $this->assertSame('service', $depot->statut_circuit);
        $this->assertSame('Zeroug', $depot->service_assigne);
        $this->assertSame('conseiller', $depot->destination_type);
        $this->assertSame('Chez le Conseiller : Zeroug', $depot->statutLabel());
    }

    public function test_cabinet_can_route_a_demande_to_a_specific_division_of_a_service(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        Orientation::create(['name' => 'Etat Civil']);
        User::factory()->create([
            'role' => UserRole::User,
            'role_kind' => 'division',
            'division_of' => 'Etat Civil',
            'name' => 'Chef Division Etat Civil',
            'service' => 'Chef Division Etat Civil',
        ]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'division',
            'destination_value' => 'Chef Division Etat Civil',
        ])->assertRedirect();

        $depot->refresh();
        $this->assertSame('service', $depot->statut_circuit);
        $this->assertSame('Chef Division Etat Civil', $depot->service_assigne);
        $this->assertSame('division', $depot->destination_type);
        $this->assertSame('Chez la Division : Chef Division Etat Civil', $depot->statutLabel());
    }

    public function test_a_demande_routed_to_one_division_is_not_visible_to_another(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        Orientation::create(['name' => 'Etat Civil']);
        $division1 = User::factory()->create(['role' => UserRole::User, 'role_kind' => 'division', 'division_of' => 'Etat Civil', 'name' => 'Division A', 'service' => 'Division A']);
        $division2 = User::factory()->create(['role' => UserRole::User, 'role_kind' => 'division', 'division_of' => 'Etat Civil', 'name' => 'Division B', 'service' => 'Division B']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'division',
            'destination_value' => 'Division A',
        ]);

        $this->actingAs($division1)->get('/circuit/service')
            ->assertViewHas('demandes', fn ($demandes) => $demandes->contains('id', $depot->id));

        $this->actingAs($division2)->get('/circuit/service')
            ->assertViewHas('demandes', fn ($demandes) => ! $demandes->contains('id', $depot->id));
    }

    public function test_an_unknown_destination_category_is_rejected(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'not_a_real_category',
        ])->assertSessionHasErrors('destination_category');

        $this->assertSame('fatou', $depot->fresh()->statut_circuit);
    }

    public function test_service_close_buttons_directly_set_the_resolution(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Etat Civil']);

        $this->actingAs($serviceUser)->post("/circuit/{$depot->id}/cloturer", ['resolution' => 'classer'])
            ->assertRedirect();

        $depot->refresh();
        $this->assertSame('cloture', $depot->statut_circuit);
        $this->assertSame('classer', $depot->resolution_service);
    }

    public function test_closing_a_demande_marks_its_service_notification_as_read(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Etat Civil']);

        $notification = \App\Models\ServiceNotification::create([
            'service' => 'Etat Civil',
            'iddmd' => $depot->id,
            'message' => 'Nouvelle demande affectée à votre service.',
        ]);

        $this->actingAs($serviceUser)->post("/circuit/{$depot->id}/cloturer", ['resolution' => 'traiter']);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_fatou_index_shows_already_annotated_demandes(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        Orientation::create(['name' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [
            'remarque_maire' => 'Dossier vu, à traiter en priorité.',
            'destination_category' => 'service',
            'destination_value' => 'Etat Civil',
        ]);

        $response = $this->actingAs($fatou)->get('/circuit/fatou');

        $response->assertOk();
        $response->assertSee('Demandes déjà annotées');
        $response->assertSee('Dossier vu, à traiter en priorité.');
        $response->assertDontSee('Aucune demande annotée pour le moment.');
    }

    public function test_decide_marks_the_demande_as_unseen_by_accueil(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", ['remarque_maire' => 'RAS']);

        $this->assertFalse($depot->fresh()->vue_accueil);
    }

    public function test_visiting_suivi_marks_annotations_as_seen_by_accueil(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'cloture', 'remarque_maire' => 'RAS', 'vue_accueil' => false]);

        $response = $this->actingAs($accueil)->get('/circuit/suivi');

        $response->assertOk();
        $this->assertTrue($depot->fresh()->vue_accueil);
    }

    public function test_accueil_bell_shows_the_new_annotations_count(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'cloture', 'remarque_maire' => 'RAS', 'vue_accueil' => false]);

        $response = $this->actingAs($accueil)->get('/');

        $response->assertOk();
        $response->assertSee("1 nouvelle(s) annotation(s) du Maire à consulter", false);
    }

    public function test_decide_requires_annotations(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($fatou)->post("/circuit/{$depot->id}/decider", [])
            ->assertSessionHasErrors('remarque_maire');

        $this->assertSame('fatou', $depot->fresh()->statut_circuit);
    }

    public function test_only_the_assigned_service_can_close_a_demande(): void
    {
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Etat Civil']);

        $otherServiceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Urbanisme']);

        $this->actingAs($otherServiceUser)
            ->post("/circuit/{$depot->id}/cloturer")
            ->assertForbidden();

        $this->assertSame('service', $depot->fresh()->statut_circuit);
    }

    public function test_closed_demandes_remain_visible_to_the_service_that_closed_them(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Etat Civil']);

        $this->actingAs($serviceUser)->post("/circuit/{$depot->id}/cloturer", ['resolution' => 'traiter'])->assertRedirect();

        $response = $this->actingAs($serviceUser)->get('/circuit/service');

        $response->assertOk();
        $response->assertViewHas('demandesTraitees', function ($demandesTraitees) use ($depot) {
            return $demandesTraitees->contains('id', $depot->id);
        });
        // Une fois clôturée, elle ne fait plus partie des demandes "à traiter".
        $response->assertViewHas('demandes', function ($demandes) use ($depot) {
            return ! $demandes->contains('id', $depot->id);
        });
    }

    public function test_cannot_close_a_demande_that_is_not_at_the_service_step(): void
    {
        $depot = $this->makeDepot();
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $this->actingAs($serviceUser)
            ->post("/circuit/{$depot->id}/cloturer")
            ->assertForbidden();
    }

    public function test_a_service_user_cannot_send_a_demande_back_to_fatou(): void
    {
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'cloture']);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $this->actingAs($serviceUser)
            ->post("/circuit/{$depot->id}/envoyer-fatou")
            ->assertForbidden();

        $this->assertSame('cloture', $depot->fresh()->statut_circuit);
    }

    public function test_cannot_send_a_demande_to_fatou_unless_it_is_still_at_accueil(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'cloture']);

        $this->actingAs($accueil)
            ->post("/circuit/{$depot->id}/envoyer-fatou")
            ->assertForbidden();

        $this->assertSame('cloture', $depot->fresh()->statut_circuit);
    }

    public function test_cannot_decide_on_a_demande_unless_it_is_at_fatou(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'cloture']);

        $this->actingAs($fatou)
            ->post("/circuit/{$depot->id}/decider", [
                'remarque_maire' => 'RAS',
                'destination_category' => 'service',
                'destination_value' => 'Etat Civil',
            ])
            ->assertForbidden();

        $this->assertSame('cloture', $depot->fresh()->statut_circuit);
    }

    public function test_accueil_cannot_decide_directly(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou']);

        $this->actingAs($accueil)
            ->post("/circuit/{$depot->id}/decider", ['remarque_maire' => 'RAS'])
            ->assertForbidden();

        $this->assertSame('fatou', $depot->fresh()->statut_circuit);
    }

    public function test_service_user_only_sees_demandes_assigned_to_their_service(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        Orientation::create(['name' => 'Etat Civil']);
        Orientation::create(['name' => 'Urbanisme']);

        $depotForMe = $this->makeDepot();
        $depotForMe->update(['statut_circuit' => 'fatou', 'reference' => 'MI/2026/001']);
        $this->actingAs($fatou)->post("/circuit/{$depotForMe->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'service',
            'destination_value' => 'Etat Civil',
        ]);

        $depotForOther = $this->makeDepot();
        $depotForOther->update(['statut_circuit' => 'fatou', 'reference' => 'MI/2026/002']);
        $this->actingAs($fatou)->post("/circuit/{$depotForOther->id}/decider", [
            'remarque_maire' => 'RAS',
            'destination_category' => 'service',
            'destination_value' => 'Urbanisme',
        ]);

        $response = $this->actingAs($serviceUser)->get('/circuit/service');

        $response->assertOk();
        $response->assertSee('MI/2026/001');
        $response->assertViewHas('demandes', function ($demandes) use ($depotForMe, $depotForOther) {
            return $demandes->contains('id', $depotForMe->id) && ! $demandes->contains('id', $depotForOther->id);
        });
    }

    public function test_suivi_shows_objet_and_last_update_date(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou', 'objet' => 'Raccordement eau quartier X']);

        $response = $this->actingAs($accueil)->get('/circuit/suivi');

        $response->assertOk();
        $response->assertSee('Raccordement eau quartier X');
        $response->assertSee($depot->fresh()->updated_at->format('d/m/Y'));
    }

    public function test_suivi_search_matches_objet_and_reference(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update([
            'statut_circuit' => 'fatou',
            'objet' => 'Raccordement eau',
            'reference' => 'MI/2026/245',
        ]);
        $other = $this->makeDepot();
        $other->update(['statut_circuit' => 'fatou', 'reference' => 'AUTRE/2026/999']);

        $byObjet = $this->actingAs($accueil)->get('/circuit/suivi?search=Raccordement');
        $byObjet->assertSee('MI/2026/245');
        $byObjet->assertDontSee('AUTRE/2026/999');

        $byReference = $this->actingAs($accueil)->get('/circuit/suivi?search=MI/2026/245');
        $byReference->assertSee('MI/2026/245');
        $byReference->assertDontSee('AUTRE/2026/999');
    }

    public function test_suivi_search_matches_the_zero_padded_id_encoded_in_the_receipt_qr_code(): void
    {
        // Le QR code du reçu encode l'id sur 6 chiffres (voir print_reçu.blade.php) :
        // un lecteur de code-barres/QR physique le tape tel quel dans le champ de
        // recherche (aucun scan par caméra n'est plus nécessaire côté navigateur).
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'fatou', 'reference' => 'MI/2026/245']);

        $qrValue = str_pad((string) $depot->id, 6, '0', STR_PAD_LEFT);

        $response = $this->actingAs($accueil)->get('/circuit/suivi?search=' . $qrValue);

        $response->assertSee('MI/2026/245');
    }

    public function test_suivi_can_be_filtered_by_statut(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);

        $chezService = $this->makeDepot();
        $chezService->update(['statut_circuit' => 'service', 'reference' => 'CHEZ-SERVICE', 'service_assigne' => 'Etat Civil']);

        $chezFatou = $this->makeDepot();
        $chezFatou->update(['statut_circuit' => 'fatou', 'reference' => 'CHEZ-FATOU']);

        $response = $this->actingAs($accueil)->get('/circuit/suivi?statut=service');

        $response->assertOk();
        $response->assertSee('CHEZ-SERVICE');
        $response->assertDontSee('CHEZ-FATOU');
    }

    public function test_cabinet_cannot_access_suivi_directly_by_url(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);

        $this->actingAs($cabinet)->get('/circuit/suivi')->assertForbidden();
    }

    public function test_service_user_cannot_access_suivi_directly_by_url(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $this->actingAs($serviceUser)->get('/circuit/suivi')->assertForbidden();
    }

    public function test_cabinet_cannot_access_circuit_service_directly_by_url(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);

        $this->actingAs($cabinet)->get('/circuit/service')->assertForbidden();
    }

    public function test_accueil_cannot_access_circuit_service_directly_by_url(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($accueil)->get('/circuit/service')->assertForbidden();
    }

    public function test_admin_can_access_circuit_service(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get('/circuit/service')->assertOk();
    }

    public function test_service_user_cannot_view_the_historique_of_a_demande_assigned_to_another_service(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Urbanisme']);

        $this->actingAs($serviceUser)->get("/circuit/{$depot->id}/historique")->assertForbidden();
    }

    public function test_service_user_can_view_the_historique_of_their_own_services_demande(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Etat Civil']);

        $this->actingAs($serviceUser)->get("/circuit/{$depot->id}/historique")->assertOk();
    }

    public function test_service_users_historique_back_link_does_not_point_to_suivi(): void
    {
        // Un utilisateur de service n'a pas accès à Suivi (réservé à Accueil/Admin) :
        // le bouton "retour" ne doit pas l'y renvoyer, sous peine de 403.
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'service', 'service_assigne' => 'Etat Civil']);

        $response = $this->actingAs($serviceUser)->get("/circuit/{$depot->id}/historique");

        $response->assertOk();
        $response->assertSee(route('circuit.service.index'), false);
        $response->assertDontSee(route('circuit.suivi'), false);
    }

    public function test_accueils_historique_back_link_points_to_suivi(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();

        $response = $this->actingAs($accueil)->get("/circuit/{$depot->id}/historique");

        $response->assertOk();
        $response->assertSee(route('circuit.suivi'), false);
    }

    public function test_suivi_page_no_longer_offers_the_camera_qr_scanner(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($accueil)->get('/circuit/suivi');

        $response->assertOk();
        $response->assertSee('scan_search', false);
        $response->assertDontSee('qrScannerModal', false);
        $response->assertDontSee('html5-qrcode', false);
    }

    public function test_cabinet_cannot_view_an_arbitrary_demandes_historique(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();

        $this->actingAs($cabinet)->get("/circuit/{$depot->id}/historique")->assertForbidden();
    }
}
