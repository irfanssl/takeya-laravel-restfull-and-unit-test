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

    /**
     * create post testing
     */
    public function test_create_posts_is_not_accessible_for_guests(): void
    {
        $this->get('/posts/create')
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    public function test_create_posts_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/posts/create')
            ->assertStatus(200);
    }

    public function test_create_posts_is_just_return_string(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/posts/create')
            ->assertStatus(200)
            ->assertSee('posts.create');
    }

    public function test_authenticated_users_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $postData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $this->actingAs($user)
            ->postJson('/posts', $postData)
            ->assertStatus(201)
            ->assertJsonFragment($postData);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_users_cannot_create_a_post(): void
    {
        $postData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $this->postJson('/posts', $postData)
            ->assertStatus(401);

        $this->assertDatabaseMissing('posts', [
            'title' => $postData['title'],
            'content' => $postData['content'],
        ]);
    }

    public function test_title_is_required_when_creating_a_post(): void
    {
        $user = User::factory()->create();

        $postData = [
            'content' => $this->faker->paragraph,
        ];

        $this->actingAs($user)
            ->postJson('/posts', $postData)
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseMissing('posts', [
            'content' => $postData['content'],
        ]);
    }

    public function test_content_is_required_when_creating_a_post(): void
    {
        $user = User::factory()->create();

        $postData = [
            'title' => $this->faker->sentence,
        ];

        $this->actingAs($user)
            ->postJson('/posts', $postData)
            ->assertStatus(422)
            ->assertJsonValidationErrors('content');

        $this->assertDatabaseMissing('posts', [
            'title' => $postData['title'],
        ]);
    }

    /**
     * edit post testing
     */
    public function test_edit_posts_is_not_accessible_for_guests(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);

        $this->get("/posts/{$post->id}/edit")
            ->assertRedirect('/login');
    }

    public function test_edit_posts_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);

        $this->actingAs($user)
            ->get("/posts/{$post->id}/edit")
            ->assertStatus(200)
            ->assertSee('posts.edit');
    }

    public function test_edit_posts_is_just_return_string(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);

        $this->actingAs($user)
            ->get("/posts/{$post->id}/edit")
            ->assertStatus(200)
            ->assertSee('posts.edit');
    }

    /**
     * update post testing
     */
    public function test_update_posts_is_not_accessible_for_guests(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);

        $updateData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $this->putJson("/posts/{$post->id}", $updateData)
            ->assertStatus(401);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
        ]);
    }

    public function test_update_posts_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);

        $updateData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $this->actingAs($user)
            ->putJson("/posts/{$post->id}", $updateData)
            ->assertStatus(201);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
            'content' => $updateData['content'],
        ]);
    }

    public function test_update_post_only_author_allowed_to_update(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);

        // Create another user (non-author)
        $nonAuthor = User::factory()->create();

        $updateData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $this->actingAs($nonAuthor)
            ->putJson("/posts/{$post->id}", $updateData)
            ->assertStatus(422);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
            'content' => $updateData['content'],
        ]);
    }

    public function test_update_post_title_is_required_when_updating_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);
        $postData = [
            'content' => $this->faker->paragraph,
        ];

        $this->actingAs($user)
            ->putJson("/posts/{$post->id}", $postData)
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseMissing('posts', [
            'content' => $postData['content'],
        ]);
    }

    public function test_update_post_content_is_required_when_upadating_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ]);
        $postData = [
            'title' => $this->faker->sentence,
        ];

        $this->actingAs($user)
            ->putJson("/posts/{$post->id}", $postData)
            ->assertStatus(422)
            ->assertJsonValidationErrors('content');

        $this->assertDatabaseMissing('posts', [
            'title' => $postData['title'],
        ]);
    }
}
