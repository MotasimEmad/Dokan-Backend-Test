<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10); // Default 10 posts per page
        $perPage = min($perPage, 100); // Maximum 100 posts per page

        $posts = Post::with(['user', 'category', 'comments' => function ($query) {
                $query->with('user')->latest()->limit(5); // Only 5 recent comments
            }])
            ->withCount('comments')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PostResource::collection($posts->items()),
            'pagination' => collect($posts)->except('data', 'links')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
        ]);

        $post->load(['user', 'category']);

        return $this->createdResponse(new PostResource($post), 'Post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): JsonResponse
    {
        $this->authorize('view', $post);
    
        $commentsPerPage = $request->get('comments_per_page', 8); // Default 8 comments per page
        $commentsPerPage = min($commentsPerPage, 100); // Maximum 100 comments per page
    
        // Load post relations without comments
        $post->load(['user', 'category']);
    
        // Paginate comments separately
        $comments = $post->comments()
            ->with('user')
            ->latest()
            ->paginate($commentsPerPage);
    
        return response()->json([
            'success' => true,
            'post' => new PostResource($post),
            'comments' => [
                'data' => CommentResource::collection($comments->items()),
                'pagination' => collect($comments)->except('data', 'links')
            ]
        ]);
    }
    

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'category_id' => $request->category_id,
        ]);

        $post->load(['user', 'category']);

        return $this->successResponse(new PostResource($post), 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return $this->deletedResponse('Post deleted successfully');
    }

    /**
     * Display posts for a specific category.
     */
    public function postsByCategory(Request $request, Category $category): JsonResponse
    {
        $perPage = $request->get('per_page', 15); // Default 15 posts per page
        $perPage = min($perPage, 100); // Maximum 100 posts per page

        $posts = $category->posts()
            ->with(['user', 'category', 'comments' => function ($query) {
                $query->with('user')->latest()->limit(5); // Only 5 recent comments
            }])
            ->withCount('comments')
            ->latest()
            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => PostResource::collection($posts->items()),
                'pagination' => collect($posts)->except('data', 'links')
            ]);
    }
}
