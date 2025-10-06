<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;

class NotificationTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_authenticated_user_can_list_their_notifications(): void {
        $user = User::factory()->worker()->create();
        $otherUser = User::factory()->worker()->create();

        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        Notification::factory()->count(2)->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/notifications');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'message',
                    'data',
                 ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(3, $data); // only user's notifications

        foreach ($data as $notification) {
            $this->assertSame($user->id, $notification['user_id']);
            $this->assertArrayHasKey('id', $notification);
            $this->assertArrayHasKey('type', $notification);
            $this->assertArrayHasKey('message', $notification);
            $this->assertArrayHasKey('created_at', $notification);
        }
    }

    public function test_unauthenticated_user_cannot_list_notifications(): void {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_mark_notification_as_read(): void {
        $user = User::factory()->worker()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
                 ->assertJson([
                    'message' => 'Notification marked as read'
                 ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void {
        $user = User::factory()->worker()->create();
        $other = User::factory()->worker()->create();
        $notification = Notification::factory()->create([
            'user_id' => $other->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    public function test_it_returns_unread_notifications_count(): void {
        $user = User::factory()->worker()->create();
        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);
        Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
                 ->assertJson([
                    'unread_count' => 2
                 ]);
    }
}
