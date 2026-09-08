<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Tabdepot;
use App\Models\Typedem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypedemTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_a_type_still_used_by_a_demande(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $typedem = Typedem::create(['name' => 'Autorisation']);
        Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22222222', 'daterecp' => now()->format('Y-m-d'), 'typdm' => 'Autorisation']);

        $response = $this->actingAs($admin)->delete("/typedem/{$typedem->id}");

        $response->assertRedirect(route('typedem.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('typedem', ['id' => $typedem->id]);
    }

    public function test_can_delete_a_type_no_longer_used_by_any_demande(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $typedem = Typedem::create(['name' => 'Autorisation']);

        $response = $this->actingAs($admin)->delete("/typedem/{$typedem->id}");

        $response->assertRedirect(route('typedem.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('typedem', ['id' => $typedem->id]);
    }
}
