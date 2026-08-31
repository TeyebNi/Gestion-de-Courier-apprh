<?php

namespace Tests\Feature;

use App\Enums\UserRole;
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

    public function test_cannot_close_a_demande_that_is_not_at_the_service_step(): void
    {
        $depot = $this->makeDepot();
        $serviceUser = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);

        $this->actingAs($serviceUser)
            ->post("/circuit/{$depot->id}/cloturer")
            ->assertForbidden();
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
}
