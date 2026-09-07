<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DemandeHistorique;
use App\Models\Orientation;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
            'nom' => 'Citoyen Test',
            'tel' => '22222222',
            'adresse' => 'Quartier Socogim, Nouakchott',
            'daterecp' => now()->format('Y-m-d'),
            'objet' => 'Raccordement eau',
            'reference' => 'MI/2026/245',
            'service_assigne' => 'Etat Civil',
        ]);

        $response = $this->actingAs($accueil)->get("/circuit/{$depot->id}/historique");

        $response->assertOk();
        $response->assertSee('Raccordement eau');
        $response->assertSee('MI/2026/245');
        $response->assertSee('22222222');
        $response->assertSee('Quartier Socogim, Nouakchott');
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

    public function test_service_index_shows_institution_name_objet_and_a_localized_date(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $pending = Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => "Ministère de l'Intérieur",
            'objet' => 'Demande de raccordement eau',
            'daterecp' => '2026-08-30',
            'statut_circuit' => 'service',
            'service_assigne' => 'Etat Civil',
        ]);
        $treated = Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => 'Ministère des Finances',
            'objet' => 'Demande de subvention',
            'daterecp' => '2026-08-15',
            'statut_circuit' => 'cloture',
            'service_assigne' => 'Etat Civil',
        ]);

        $response = $this->actingAs($serviceUser)->get('/circuit/service');

        $response->assertOk();
        $response->assertSee("Ministère de l&#039;Intérieur", false);
        $response->assertSee('Demande de raccordement eau');
        $response->assertSee('30/08/2026');
        $response->assertSee('Ministère des Finances');
        $response->assertSee('Demande de subvention');
        $response->assertSee('15/08/2026');
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

    public function test_maire_form_preselects_service_for_an_internal_demande(): void
    {
        $maire = User::factory()->create(['role' => UserRole::Maire]);
        Orientation::create(['name' => 'Informatique']);
        Orientation::create(['name' => 'Etat Civil']);
        Tabdepot::create([
            'nom' => 'Agent Test',
            'tel' => '22222222',
            'daterecp' => now()->format('Y-m-d'),
            'origine' => 'interne',
            'origine_detail' => 'Informatique',
            'statut_circuit' => 'maire',
        ]);

        $response = $this->actingAs($maire)->get('/circuit/maire');

        $response->assertOk();
        $response->assertSee('<option value="Informatique" selected>Informatique</option>', false);
        $response->assertSee('<option value="Etat Civil" >Etat Civil</option>', false);
    }

    public function test_maire_index_shows_institution_name_objet_and_a_localized_date(): void
    {
        $maire = User::factory()->create(['role' => UserRole::Maire]);
        Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => "Ministère de l'Intérieur",
            'objet' => 'Demande de raccordement eau',
            'reference' => 'MI/2026/245',
            'daterecp' => '2026-08-30',
            'statut_circuit' => 'maire',
        ]);

        $response = $this->actingAs($maire)->get('/circuit/maire');

        $response->assertOk();
        $response->assertSee("Ministère de l&#039;Intérieur", false);
        $response->assertSee('Demande de raccordement eau');
        $response->assertSee('MI/2026/245');
        $response->assertSee('30/08/2026');
        $response->assertDontSee('2026-08-30');
    }

    public function test_historique_transferts_are_paginated_by_ten(): void
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
            return $historiques->count() === 10 && $historiques->total() === 15;
        });
    }

    public function test_full_circuit_accueil_to_fatou_to_maire_to_service(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $maire = User::factory()->create(['role' => UserRole::Maire]);

        $depot = $this->makeDepot();

        // Accueil -> Fatou
        $this->actingAs($accueil)
            ->post("/circuit/{$depot->id}/envoyer-fatou")
            ->assertRedirect();
        $this->assertSame('fatou', $depot->fresh()->statut_circuit);

        // Fatou -> Maire
        $this->actingAs($fatou)
            ->post("/circuit/{$depot->id}/envoyer-maire")
            ->assertRedirect();
        $this->assertSame('maire', $depot->fresh()->statut_circuit);

        // Maire decides and sends to a service
        $this->actingAs($maire)
            ->post("/circuit/{$depot->id}/decider", [
                'decision_maire' => 'accepte',
                'remarque_maire' => 'RAS',
                'service_destination' => 'Etat Civil',
            ])
            ->assertRedirect();

        $depot->refresh();
        $this->assertSame('service', $depot->statut_circuit);
        $this->assertSame('accepte', $depot->decision_maire);
        $this->assertSame('Etat Civil', $depot->service_assigne);

        // Le service clôture la demande : fin du circuit.
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $this->actingAs($serviceUser)
            ->post("/circuit/{$depot->id}/cloturer")
            ->assertRedirect();

        $this->assertSame('cloture', $depot->fresh()->statut_circuit);

        // Every transition must be logged in the history trail.
        $this->assertGreaterThanOrEqual(4, $depot->historiques()->count());
    }

    public function test_sms_is_sent_on_decision_and_on_closure(): void
    {
        $maire = User::factory()->create(['role' => UserRole::Maire]);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'maire']);

        $this->actingAs($maire)->post("/circuit/{$depot->id}/decider", [
            'decision_maire' => 'accepte',
            'service_destination' => 'Etat Civil',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'twilio.com')
                && str_contains($request['Body'] ?? '', 'acceptée');
        });

        $this->actingAs($serviceUser)->post("/circuit/{$depot->id}/cloturer");

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'twilio.com')
                && str_contains($request['Body'] ?? '', 'traitée');
        });
    }

    public function test_decide_notifies_the_assigned_service(): void
    {
        $maire = User::factory()->create(['role' => UserRole::Maire]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'maire']);

        $this->actingAs($maire)->post("/circuit/{$depot->id}/decider", [
            'decision_maire' => 'accepte',
            'service_destination' => 'Etat Civil',
        ]);

        $this->assertDatabaseHas('service_notifications', [
            'service' => 'Etat Civil',
            'iddmd' => $depot->id,
            'is_read' => false,
        ]);
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

        $this->actingAs($serviceUser)->post("/circuit/{$depot->id}/cloturer")->assertRedirect();

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

    public function test_cannot_send_a_demande_to_maire_unless_it_is_at_fatou(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'cloture']);

        $this->actingAs($fatou)
            ->post("/circuit/{$depot->id}/envoyer-maire")
            ->assertForbidden();

        $this->assertSame('cloture', $depot->fresh()->statut_circuit);
    }

    public function test_cannot_decide_on_a_demande_unless_it_is_at_maire(): void
    {
        $maire = User::factory()->create(['role' => UserRole::Maire]);
        $depot = $this->makeDepot();
        $depot->update(['statut_circuit' => 'cloture']);

        $this->actingAs($maire)
            ->post("/circuit/{$depot->id}/decider", [
                'decision_maire' => 'accepte',
                'service_destination' => 'Etat Civil',
            ])
            ->assertForbidden();

        $this->assertSame('cloture', $depot->fresh()->statut_circuit);
    }

    public function test_accueil_cannot_send_directly_to_maire(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = $this->makeDepot();

        $this->actingAs($accueil)
            ->post("/circuit/{$depot->id}/envoyer-maire")
            ->assertForbidden();

        $this->assertSame('accueil', $depot->fresh()->statut_circuit);
    }

    public function test_service_user_only_sees_demandes_assigned_to_their_service(): void
    {
        $maire = User::factory()->create(['role' => UserRole::Maire]);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $depotForMe = $this->makeDepot();
        $depotForMe->update(['statut_circuit' => 'maire']);
        $this->actingAs($maire)->post("/circuit/{$depotForMe->id}/decider", [
            'decision_maire' => 'accepte',
            'service_destination' => 'Etat Civil',
        ]);

        $depotForOther = $this->makeDepot();
        $depotForOther->update(['statut_circuit' => 'maire']);
        $this->actingAs($maire)->post("/circuit/{$depotForOther->id}/decider", [
            'decision_maire' => 'accepte',
            'service_destination' => 'Urbanisme',
        ]);

        $response = $this->actingAs($serviceUser)->get('/circuit/service');

        $response->assertOk();
        $response->assertSee('Citoyen Test');
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
        $other->update(['statut_circuit' => 'fatou', 'nom' => 'Autre Citoyen']);

        $byObjet = $this->actingAs($accueil)->get('/circuit/suivi?search=Raccordement');
        $byObjet->assertSee('Citoyen Test');
        $byObjet->assertDontSee('Autre Citoyen');

        $byReference = $this->actingAs($accueil)->get('/circuit/suivi?search=MI/2026/245');
        $byReference->assertSee('Citoyen Test');
        $byReference->assertDontSee('Autre Citoyen');
    }

    public function test_suivi_can_be_filtered_by_statut(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);

        $chezMaire = $this->makeDepot();
        $chezMaire->update(['statut_circuit' => 'maire', 'nom' => 'Chez Maire']);

        $chezFatou = $this->makeDepot();
        $chezFatou->update(['statut_circuit' => 'fatou', 'nom' => 'Chez Fatou']);

        $response = $this->actingAs($accueil)->get('/circuit/suivi?statut=maire');

        $response->assertOk();
        $response->assertSee('Chez Maire');
        $response->assertDontSee('Chez Fatou');
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

    public function test_cabinet_cannot_view_an_arbitrary_demandes_historique(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);
        $depot = $this->makeDepot();

        $this->actingAs($cabinet)->get("/circuit/{$depot->id}/historique")->assertForbidden();
    }
}
