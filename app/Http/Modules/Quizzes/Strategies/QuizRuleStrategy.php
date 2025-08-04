<?php

namespace App\Http\Modules\Quizzes\Strategies;

use App\Components\Abstracts\RuleStrategy;

class QuizRuleStrategy extends RuleStrategy
{
    public function getPrimitiveRules(): array
    {
        return [
            "title" => "string|max:255",
            "slug" => "string|max:255",
            "description" => "nullable|string",
            "is_public" => "boolean",
            "level_id" => "integer|exists:quiz_levels,id",
            "status" => "in:draft,published,archived",
            "user_id" => "integer|exists:users,id",
            "duration" => "nullable|integer|min:1",
            "pass_score" => "nullable|integer|min:0|max:100",
            "thumbnail" => "nullable|string|max:255",
            "category_id" => "integer|exists:categories,id",
        ];
    }

    public function getRules(): array
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
}
