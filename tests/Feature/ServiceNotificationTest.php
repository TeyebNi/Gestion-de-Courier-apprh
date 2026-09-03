<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ServiceNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_sees_notifications_for_their_own_service(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        ServiceNotification::create(['service' => 'Etat Civil', 'message' => 'Pour vous']);
        ServiceNotification::create(['service' => 'Urbanisme', 'message' => 'Pas pour vous']);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertOk();
        $response->assertSee('Pour vous');
        $response->assertDontSee('Pas pour vous');
    }

    public function test_user_cannot_mark_another_services_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $notification = ServiceNotification::create(['service' => 'Urbanisme', 'message' => 'Pas pour vous']);

        $this->actingAs($user)
            ->patch("/notifications/{$notification->id}/read")
            ->assertForbidden();

        $this->assertFalse($notification->fresh()->is_read);
    }

    public function test_user_can_mark_their_own_service_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        $notification = ServiceNotification::create(['service' => 'Etat Civil', 'message' => 'Pour vous']);

        $this->actingAs($user)
            ->patch("/notifications/{$notification->id}/read")
            ->assertRedirect(route('notifications.index'));

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_admin_sees_notifications_from_every_service(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        ServiceNotification::create(['service' => 'Etat Civil', 'message' => 'Message A']);
        ServiceNotification::create(['service' => 'Urbanisme', 'message' => 'Message B']);

        $response = $this->actingAs($admin)->get('/notifications');

        $response->assertOk();
        $response->assertSee('Message A');
        $response->assertSee('Message B');
    }

    public function test_sidebar_shows_unread_notification_count_for_service_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'service' => 'Etat Civil']);
        ServiceNotification::create(['service' => 'Etat Civil', 'message' => 'Non lue', 'is_read' => false]);
        ServiceNotification::create(['service' => 'Etat Civil', 'message' => 'Déjà lue', 'is_read' => true]);

        $response = $this->actingAs($user)->get('/circuit/service');

        $response->assertOk();
        $response->assertSee(route('notifications.index'), false);
        $response->assertSee('badge-danger');
        $response->assertSee('>1<', false);
    }
}
