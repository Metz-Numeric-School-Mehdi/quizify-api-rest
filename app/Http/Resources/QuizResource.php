<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "slug" => $this->slug,
            "description" => $this->description,
            "is_public" => $this->is_public,
            "level" => $this->level,
            "user_id" => $this->user_id,
            "duration" => $this->duration,
            "max_attempts" => $this->max_attempts,
            "pass_score" => $this->pass_score,
            "thumbnail" => $this->thumbnail,
            "tags" => $this->tags,
            "category" => $this->category,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
