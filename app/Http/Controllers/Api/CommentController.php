<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    use ApiResponse;
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $this->authorize('create', Comment::class);

        $comment = Comment::create([
            'content' => $request->content,
            'user_id' => auth()->id(),
            'post_id' => $post->id,
        ]);

        $comment->load('user');

        return $this->createdResponse(new CommentResource($comment), 'Comment created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment): JsonResponse
    {
        $this->authorize('view', $comment);

        $comment->load('user');

        return $this->successResponse(new CommentResource($comment));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load('user');

        return $this->successResponse(new CommentResource($comment), 'Comment updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return $this->deletedResponse('Comment deleted successfully');
    }
}
