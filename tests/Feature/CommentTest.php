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
}
