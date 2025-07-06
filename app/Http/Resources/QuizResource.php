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
            "pass_score" => $this->pass_score,
            "thumbnail" => $this->thumbnail,
            "thumbnail_url" => $this->thumbnail
                ? (\Storage::disk('minio')->exists($this->thumbnail)
                    ? \Storage::disk('minio')->temporaryUrl($this->thumbnail, now()->addMinutes(60))
                    : null)
                : null,
            "tags" => $this->tags,
            "category" => $this->category,
            "questions" => $this->whenLoaded('questions', function () {
                return $this->questions->map(function ($question) {
                    return [
                        "id" => $question->id,
                        "content" => $question->content,
                        "question_type_id" => $question->question_type_id ?? null,
                        "answers" => $question->answers->map(function ($answer) {
                            return [
                                "id" => $answer->id,
                                "content" => $answer->content,
                                "is_correct" => $answer->is_correct,
                            ];
                        }),
                    ];
                });
            }),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "status" => $this->status,
        ];
    }
}
