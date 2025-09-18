<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $comments = $this->whenLoaded('comments');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'user' => [
    'id' => $this->user->id,
    'name' => $this->user->name,
],
'category' => [
    'id' => $this->category->id,
    'name' => $this->category->name,
],

            'comments_count' => $this->when($this->comments_count !== null, $this->comments_count),
            'comments' => CommentResource::collection($comments),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
