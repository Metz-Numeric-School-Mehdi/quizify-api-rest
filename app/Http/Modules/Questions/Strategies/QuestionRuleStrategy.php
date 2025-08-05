<?php

namespace App\Http\Modules\Questions\Strategies;

use App\Components\Abstracts\RuleStrategy;

class QuestionRuleStrategy extends RuleStrategy
{
    /**
     * Rules for creating a question.
     */
    public function getCreateRules(): array
    {
        return [
            "quiz_id" => "required|integer|exists:quizzes,id",
            "content" => "required|string|max:255",
            "question_type_id" => "required|integer|exists:question_types,id",
        ];
    }

    /**
     * Rules for updating a question.
     * @param int|null $id The question ID (not used for questions but kept for consistency)
     */
    public function getUpdateRules(?int $id = null): array
    {
        return [
            "quiz_id" => "sometimes|required|integer|exists:quizzes,id",
            "content" => "sometimes|required|string|max:255",
            "question_type_id" => "sometimes|required|integer|exists:question_types,id",
        ];
    }

    /**
     * Get the validation rules for a collection of question data.
     *
     * @return array
     */
    public function getDataCollectionRules(): array
    {
        return [
            "*.quiz_id" => "required|integer|exists:quizzes,id",
            "*.content" => "required|string|max:255",
            "*.question_type_id" => "required|integer|exists:question_types,id",
        ];
    }
}
