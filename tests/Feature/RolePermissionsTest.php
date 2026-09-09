<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DemandeHistorique;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_non_admin_cannot_access_admin_config_pages(): void
    {
        $fatou = User::factory()->create(['role' => UserRole::Fatou]);

        $this->actingAs($fatou)->get('/utilisateurs')->assertForbidden();
    }

    public function test_admin_can_access_every_role_gated_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->get('/circuit/fatou')->assertOk();
        $this->actingAs($admin)->get('/utilisateurs')->assertOk();
    }

    public function test_admin_without_can_manage_users_keeps_other_admin_access(): void
    {
        $restrictedAdmin = User::factory()->create(['role' => UserRole::Admin, 'can_manage_users' => false]);

        $this->actingAs($restrictedAdmin)->get('/utilisateurs')->assertForbidden();
        $this->actingAs($restrictedAdmin)->get('/orientation')->assertOk();
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

    public function test_restricted_admin_like_accueil_loses_cabinet_and_all_services_access(): void
    {
        $accueilLikeAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'can_access_cabinet' => false,
            'can_access_all_services' => false,
        ]);

        $this->actingAs($accueilLikeAdmin)->get('/circuit/fatou')->assertForbidden();
        // Toujours admin : garde Orientation/Dépôt.
        $this->actingAs($accueilLikeAdmin)->get('/orientation')->assertOk();
        $this->actingAs($accueilLikeAdmin)->get('/depot')->assertOk();
    }

    public function test_restricted_admin_sees_the_minimal_accueil_dashboard(): void
    {
        $accueilLikeAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'can_manage_users' => false,
            'can_access_cabinet' => false,
            'can_access_all_services' => false,
        ]);

        $response = $this->actingAs($accueilLikeAdmin)->get('/');

        $response->assertOk();
        $response->assertSee('Guichet');
    }

    public function test_admin_missing_only_can_manage_users_still_sees_the_full_dashboard(): void
    {
        // Manquer uniquement can_manage_users ne retire rien côté Cabinet de Maire
        // ou Services : ce n'est pas un profil "Accueil" et ne doit pas être
        // réduit au dashboard minimal.
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'can_manage_users' => false,
        ]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertDontSee('Guichet');
        $response->assertSee('Nombre de Demandes');
    }

    public function test_unrestricted_admin_still_sees_the_full_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertDontSee('Guichet');
        $response->assertSee('Nombre de Demandes');
    }

    public function test_dashboard_does_not_leak_raw_demande_list_to_cabinet(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);

        $cabinetResponse = $this->actingAs($cabinet)->get('/');
        $cabinetResponse->assertOk();
        $cabinetResponse->assertSee("En attente d'annotations");
        $cabinetResponse->assertDontSee('Dernières Demandes Déposées');
        $cabinetResponse->assertDontSee(route('depot.index'), false);
    }

    public function test_cabinet_mini_dashboard_shows_total_annotees(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);

        $depotA = Tabdepot::create(['nom' => 'A', 'tel' => '22222222', 'daterecp' => now()->format('Y-m-d')]);
        $depotB = Tabdepot::create(['nom' => 'B', 'tel' => '22222223', 'daterecp' => now()->format('Y-m-d')]);

        $this->actingAs($accueil)->post("/circuit/{$depotA->id}/envoyer-fatou");
        $this->actingAs($accueil)->post("/circuit/{$depotB->id}/envoyer-fatou");
        $this->actingAs($cabinet)->post("/circuit/{$depotA->id}/decider", ['remarque_maire' => 'Vu, à traiter.']);
        $this->actingAs($cabinet)->post("/circuit/{$depotB->id}/decider", ['remarque_maire' => 'Vu, à traiter.', 'service_destination' => 'Etat Civil']);

        $response = $this->actingAs($cabinet)->get('/');

        $response->assertOk();
        $response->assertSee('Annotées');
        $response->assertSee('2');
    }

    public function test_cabinet_does_not_see_suivi_des_demandes_link(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);

        $response = $this->actingAs($cabinet)->get('/');

        $response->assertOk();
        $response->assertDontSee('Suivi des Demandes');
    }

    public function test_cabinet_mini_dashboard_does_not_show_decision_totals(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);

        $response = $this->actingAs($cabinet)->get('/');

        $response->assertOk();
        $response->assertDontSee('Mes décisions');
    }

    public function test_service_dashboard_hides_the_type_and_acceptance_charts(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $response = $this->actingAs($serviceUser)->get('/');

        $response->assertOk();
        $response->assertDontSee('Type de Demande');
        $response->assertDontSee('Acceptées / Refusées');
        $response->assertDontSee("Taux d'Acceptation", false);
        $response->assertSee('Évolution des Demandes');
    }

    public function test_admin_dashboard_no_longer_shows_type_or_acceptance_charts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertDontSee('Type de Demande');
        $response->assertDontSee('Acceptées / Refusées');
        $response->assertDontSee("Taux d'Acceptation", false);
        $response->assertSee('Évolution des Demandes');
    }

    public function test_service_dashboard_recent_demandes_are_ordered_and_labeled_by_last_update(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $old = Tabdepot::create(['reference' => 'ANCIEN-1', 'daterecp' => '2026-01-01', 'service_assigne' => 'Etat Civil']);
        $recent = Tabdepot::create(['reference' => 'RECENT-1', 'daterecp' => '2026-01-01', 'service_assigne' => 'Etat Civil']);
        // Eloquent ecrase updated_at a chaque save() : on force la valeur directement
        // en base pour simuler un ordre de derniere mise a jour realiste.
        \Illuminate\Support\Facades\DB::table('tabdepot')->where('id', $old->id)->update(['updated_at' => now()->subDays(10)]);
        \Illuminate\Support\Facades\DB::table('tabdepot')->where('id', $recent->id)->update(['updated_at' => now()]);

        $response = $this->actingAs($serviceUser)->get('/');

        $response->assertOk();
        $response->assertSee('Dernière mise à jour');
        $response->assertSeeInOrder(['RECENT-1', 'ANCIEN-1']);
    }

    public function test_accueil_sees_a_navbar_bell_with_the_pending_count(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        Tabdepot::create(['nom' => 'A', 'tel' => '22222222', 'daterecp' => now()->format('Y-m-d')]);
        Tabdepot::create(['nom' => 'B', 'tel' => '22222223', 'daterecp' => now()->format('Y-m-d')]);
        Tabdepot::create(['nom' => 'C', 'tel' => '22222224', 'daterecp' => now()->format('Y-m-d')]);

        $response = $this->actingAs($accueil)->get('/');

        $response->assertOk();
        $response->assertSee('circuitBellDropdown', false);
        $response->assertSee('3 demande(s) à transmettre au Cabinet');
    }

    public function test_cabinet_sees_a_navbar_bell_with_the_pending_count(): void
    {
        $cabinet = User::factory()->create(['role' => UserRole::Fatou]);
        Tabdepot::create(['nom' => 'A', 'tel' => '22222222', 'daterecp' => now()->format('Y-m-d'), 'statut_circuit' => 'fatou']);
        Tabdepot::create(['nom' => 'B', 'tel' => '22222223', 'daterecp' => now()->format('Y-m-d'), 'statut_circuit' => 'fatou']);

        $response = $this->actingAs($cabinet)->get('/');

        $response->assertOk();
        $response->assertSee('circuitBellDropdown', false);
        $response->assertSee("2 demande(s) en attente d'annotations", false);
    }

    public function test_plain_service_user_does_not_see_the_circuit_bell(): void
    {
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $response = $this->actingAs($serviceUser)->get('/');

        $response->assertOk();
        $response->assertDontSee('circuitBellDropdown', false);
    }

    public function test_accueil_mini_dashboard_shows_the_corbeille_count(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        $depot = Tabdepot::create(['nom' => 'A', 'tel' => '22222222', 'daterecp' => now()->format('Y-m-d')]);
        $depot->delete();

        $response = $this->actingAs($accueil)->get('/');

        $response->assertOk();
        $response->assertSee('En Corbeille');
        $response->assertSee(route('depot.trashed'), false);
    }

    public function test_accueil_mini_dashboard_no_longer_shows_type_or_acceptance_charts(): void
    {
        $accueil = User::factory()->create(['role' => UserRole::User]);
        Tabdepot::create(['reference' => 'A', 'daterecp' => now()->format('Y-m-d')]);
        Tabdepot::create(['reference' => 'B', 'daterecp' => now()->format('Y-m-d')]);

        $response = $this->actingAs($accueil)->get('/');

        $response->assertOk();
        $response->assertDontSee('Type de Demande');
        $response->assertSee('Évolution des Demandes');
        $response->assertDontSee('Acceptées / Refusées');
        $response->assertDontSee("Taux d'Acceptation", false);
    }

    public function test_accueil_mini_dashboard_shows_origine_badge(): void
    {
        $accueilLikeAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'can_manage_users' => false,
            'can_access_cabinet' => false,
            'can_access_all_services' => false,
        ]);
        Tabdepot::create([
            'reference' => 'MI/2026/010',
            'daterecp' => now()->format('Y-m-d'),
            'origine' => 'interne',
            'origine_detail' => 'Etat Civil',
        ]);

        $response = $this->actingAs($accueilLikeAdmin)->get('/');

        $response->assertOk();
        $response->assertSee('Interne');
        $response->assertSee('Etat Civil');
        $response->assertSee('MI/2026/010');
    }

    public function test_accueil_mini_dashboard_shows_institution_name_objet_and_a_localized_date(): void
    {
        $accueilLikeAdmin = User::factory()->create([
            'role' => UserRole::Admin,
            'can_manage_users' => false,
            'can_access_cabinet' => false,
            'can_access_all_services' => false,
        ]);
        Tabdepot::create([
            'origine' => 'externe',
            'type_expediteur' => 'institution',
            'origine_detail' => "Ministère de l'Intérieur",
            'objet' => 'Demande de raccordement eau',
            'daterecp' => '2026-08-30',
        ]);

        $response = $this->actingAs($accueilLikeAdmin)->get('/');

        $response->assertOk();
        $response->assertSee("Ministère de l&#039;Intérieur", false);
        $response->assertSee('Demande de raccordement eau');
        $response->assertSee('30/08/2026');
        $response->assertDontSee('2026-08-30');
    }

    public function test_is_maire_adjoint_matches_the_shared_label(): void
    {
        $adjointUser = User::factory()->create(['role' => UserRole::User, 'service' => User::MAIRE_ADJOINT_LABEL]);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $accueil = User::factory()->create(['role' => UserRole::User]);

        $this->assertTrue($adjointUser->isMaireAdjoint());
        $this->assertFalse($serviceUser->isMaireAdjoint());
        $this->assertFalse($accueil->isMaireAdjoint());
    }

    public function test_sidebar_shows_maire_adjoint_label_instead_of_demandes_du_circuit(): void
    {
        $adjointUser = User::factory()->create(['role' => UserRole::User, 'service' => User::MAIRE_ADJOINT_LABEL]);
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $adjointResponse = $this->actingAs($adjointUser)->get('/');
        $adjointResponse->assertSee('Adjoint au Maire');
        $adjointResponse->assertDontSee('Demandes du Circuit');

        $serviceResponse = $this->actingAs($serviceUser)->get('/');
        $serviceResponse->assertSee('Demandes du Circuit');
        $serviceResponse->assertDontSee('Adjoint au Maire');
    }

    public static function otherSpecialLabelsProvider(): array
    {
        return [
            'Division' => [User::DIVISION_LABEL],
            'Conseiller' => [User::CONSEILLER_LABEL],
        ];
    }

    #[DataProvider('otherSpecialLabelsProvider')]
    public function test_sidebar_shows_the_other_special_role_labels(string $label): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'service' => $label]);

        $response = $this->actingAs($user)->get('/');

        $response->assertSee($label);
        $response->assertDontSee('Demandes du Circuit');
    }
}
