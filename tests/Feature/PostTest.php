<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_list_all_posts(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        Post::factory()->count(3)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'user',
                        'category',
                        'comments_count',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ]
            ]);
    }

    public function test_can_view_single_post_with_comments(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'post' => [
                    'id',
                    'title',
                    'content',
                    'user' => ['id', 'name'],
                    'category' => ['id', 'name'],
                    'created_at',
                    'updated_at',
                ],
                'comments' => [
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'user' => ['id', 'name'],
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'from',
                        'to',
                    ]
                ]
            ]);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        
        Sanctum::actingAs($user);

        $postData = [
            'title' => 'Test Post Title',
            'content' => 'This is test post content.',
            'category_id' => $category->id,
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'content',
                    'user',
                    'category',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
            'content' => 'This is test post content.',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $category = Category::factory()->create();
        
        $postData = [
            'title' => 'Test Post Title',
            'content' => 'This is test post content.',
            'category_id' => $category->id,
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(401);
    }
}
