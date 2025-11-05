<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /**
     *  index post testing
     */
    public function test_posts_index_is_accessible_for_guest(): void
    {
        $this->get('/posts')
            ->assertStatus(200)
            ->assertJson([]);
    }

    public function test_posts_index_is_accessible_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/posts')
            ->assertStatus(200)
            ->assertJson([]);
    }
}
