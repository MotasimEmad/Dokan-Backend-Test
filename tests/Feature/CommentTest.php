<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_authenticated_user_can_create_comment(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'category_id' => $category->id,
        ]);
        
        Sanctum::actingAs($user);

        $commentData = [
            'content' => 'This is a test comment.',
        ];

        $response = $this->postJson("/api/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'content',
                    'user' => ['id', 'name'],
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'This is a test comment.',
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_comment(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'category_id' => $category->id,
        ]);
        
        $commentData = [
            'content' => 'This is a test comment.',
        ];

        $response = $this->postJson("/api/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(401);
    }

    public function test_comment_owner_can_update_comment(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'category_id' => $category->id,
        ]);
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
        
        Sanctum::actingAs($user);

        $updateData = [
            'content' => 'This is an updated comment.',
        ];

        $response = $this->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'content',
                    'user' => ['id', 'name'],
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'This is an updated comment.',
        ]);
    }

    public function test_non_owner_cannot_update_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'category_id' => $category->id,
        ]);
        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'post_id' => $post->id,
        ]);
        
        Sanctum::actingAs($otherUser);

        $updateData = [
            'content' => 'This is an updated comment.',
        ];

        $response = $this->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_comment_owner_can_delete_comment(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'category_id' => $category->id,
        ]);
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
        
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_non_owner_cannot_delete_comment(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'category_id' => $category->id,
        ]);
        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'post_id' => $post->id,
        ]);
        
        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_comment_creation_requires_validation(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'category_id' => $category->id,
        ]);
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/posts/{$post->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }
}
