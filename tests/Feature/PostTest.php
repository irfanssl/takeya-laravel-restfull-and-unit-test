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

    /**
     * detail post testing
     */
    public function test_post_detail_is_accessible_for_guests(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'is_draft' => 0,
            'content' => $this->faker->paragraph,
        ]);

        $this->getJson("/posts/{$post->id}")
            ->assertStatus(201)
            ->assertJson([]);
    }

    public function test_post_detail_is_accessible_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'is_draft' => 0,
            'content' => $this->faker->paragraph,
        ]);

        $this->actingAs($user)
            ->getJson("/posts/{$post->id}")
            ->assertStatus(201)
            ->assertJson([]);
    }

    public function test_post_detail_should_return_404_if_it_is_a_draft_post(): void
    {
        $user = User::factory()->create();
        $draftPost = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'is_draft' => 1,
        ]);

        $this->getJson("/posts/{$draftPost->id}")
            ->assertStatus(404);
    }

    public function test_post_detail_should_return_201_if_it_is_an_active_post(): void
    {
        $user = User::factory()->create();
        $draftPost = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'is_draft' => 0,
        ]);

        $this->getJson("/posts/{$draftPost->id}")
            ->assertStatus(201);
    }
}
