<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Events;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_can_view_events_authenticated(): void {
        $user = $this->getFactoryUser();

        $response = $this
            ->actingAs($user)
            ->getJson('/api/events');

        $response->assertStatus(200);
    }

    public function test_cannot_view_events_unauthenticated(): void {
        $response = $this->getJson('/api/events');

        $response->assertStatus(401);
    }

    public function test_can_view_event_authenticated(): void {
        $user = $this->getFactoryUser();

        $events = Cache::remember('all_events', 3600, function () {
            return Events::all();
        });

        $id = $events[0]->id;

        $response = $this
            ->actingAs($user)
            ->getJson("/api/events/$id");

        $response->assertStatus(200);
    }

    private function getFactoryUser(): User {
        $user = User::factory()->create([
            'name' => 'pepe',
            'password' => bcrypt('password123')
        ]);

        return $user;
    }
}
