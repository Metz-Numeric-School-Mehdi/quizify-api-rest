<?php

namespace App\Http\Modules\Quizzes\Strategies;

use App\Components\Abstracts\RuleStrategy;

use Illuminate\Validation\Rule;

class QuizRuleStrategy extends RuleStrategy
{
    /**
     * Rules for creating a quiz.
     */
    public function getCreateRules(): array
    {
        return [
            "title" => "required|string|max:255",
            "slug" => "required|string|max:255|unique:quizzes,slug",
            "description" => "nullable|string",
            "is_public" => "required|boolean",
            "level_id" => "required|integer|exists:quiz_levels,id",
            "status" => "required|in:draft,published,archived",
            "user_id" => "required|integer|exists:users,id",
            "duration" => "nullable|integer|min:1",
            "pass_score" => "nullable|integer|min:0|max:100",
            "thumbnail" => "nullable|string|max:255",
            "category_id" => "required|integer|exists:categories,id",
        ];
    }

    /**
     * Rules for updating a quiz.
     * @param int|null $id The quiz ID to ignore for slug uniqueness
     */
    public function getUpdateRules(?int $id = null): array
    {
        return [
            "title" => "sometimes|required|string|max:255",
            "slug" => [
                "sometimes",
                "required",
                "string",
                "max:255",
                Rule::unique("quizzes", "slug")->ignore($id),
            ],
            "description" => "nullable|string",
            "is_public" => "sometimes|required|boolean",
            "level_id" => "sometimes|required|integer|exists:quiz_levels,id",
            "status" => "sometimes|required|in:draft,published,archived",
            "user_id" => "sometimes|required|integer|exists:users,id",
            "duration" => "nullable|integer|min:1",
            "pass_score" => "nullable|integer|min:0|max:100",
            "thumbnail" => "nullable|string|max:255",
            "category_id" => "sometimes|required|integer|exists:categories,id",
        ];
    }

    /**
     * Get the validation rules for a collection of quiz data.
     *
     * @return array
     */
    public function getDataCollectionRules(): array
    {
        return [
            "*.title" => "required|string|max:255",
            "*.slug" => "required|string|max:255|unique:quizzes,slug",
            "*.description" => "nullable|string",
            "*.is_public" => "required|boolean",
            "*.level_id" => "required|integer|exists:quiz_levels,id",
            "*.status" => "required|in:draft,published,archived",
            "*.user_id" => "required|integer|exists:users,id",
            "*.duration" => "nullable|integer|min:1",
            "*.pass_score" => "nullable|integer|min:0|max:100",
            "*.thumbnail" => "nullable|string|max:255",
            "*.category_id" => "required|integer|exists:categories,id",
        ];
    }

    /**
     * Get the minimal rules (for creation).
     */
    public function getRules(): array
    {
        return $this->getCreateRules();
    }

    /**
     * Get the primitive rules (for update).
     */
    public function getPrimitiveRules(): array
    {
        return $this->getUpdateRules();
    }
}
