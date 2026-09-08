<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Orientation;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrientationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_an_orientation_assigned_to_a_demande(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $orientation = Orientation::create(['name' => 'Etat Civil']);
        Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22222222', 'daterecp' => now()->format('Y-m-d'), 'service_assigne' => 'Etat Civil']);

        $response = $this->actingAs($admin)->delete("/orientation/{$orientation->id}");

        $response->assertRedirect(route('orientation.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('orientation', ['id' => $orientation->id]);
    }

    public function test_cannot_delete_an_orientation_used_as_internal_origine_detail(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $orientation = Orientation::create(['name' => 'Informatique']);
        Tabdepot::create(['nom' => 'Ahmed', 'tel' => '22222222', 'daterecp' => now()->format('Y-m-d'), 'origine' => 'interne', 'origine_detail' => 'Informatique']);

        $response = $this->actingAs($admin)->delete("/orientation/{$orientation->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('orientation', ['id' => $orientation->id]);
    }

    public function test_cannot_delete_an_orientation_assigned_to_a_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $orientation = Orientation::create(['name' => 'Urbanisme']);
        User::factory()->create(['role' => UserRole::User, 'service' => 'Urbanisme']);

        $response = $this->actingAs($admin)->delete("/orientation/{$orientation->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('orientation', ['id' => $orientation->id]);
    }

    public function test_can_delete_an_orientation_no_longer_used_anywhere(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $orientation = Orientation::create(['name' => 'Urbanisme']);

        $response = $this->actingAs($admin)->delete("/orientation/{$orientation->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('orientation', ['id' => $orientation->id]);
    }
}
