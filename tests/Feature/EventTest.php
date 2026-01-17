<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Events;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->createInitialEvent();

        $user = $this->getFactoryUser();

        $id = 1;

        $response = $this
            ->actingAs($user)
            ->getJson('/api/events/'.$id);

        $response->assertStatus(200);
    }

    public function test_error_when_event_doesnt_exist(): void {
        $this->createInitialEvent();

        $user = $this->getFactoryUser();

        $id = 12;

        $response = $this
            ->actingAs($user)
            ->getJson('/api/events/'.$id);

        $response->assertStatus(404);
    }


    private function getFactoryUser(): User {
        $user = User::factory()->create([
            'name' => 'pepe',
            'password' => bcrypt('password123')
        ]);

        return $user;
    }

    private function createInitialEvent() {
        $data = [
            'name' => 'Event Name',
            'organization' => 'Organization Name'
        ];
        Events::create($data);
    }
}
