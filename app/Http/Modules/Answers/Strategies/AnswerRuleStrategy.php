<?php

namespace App\Http\Modules\Answers\Strategies;

use App\Components\Abstracts\RuleStrategy;

class AnswerRuleStrategy extends RuleStrategy
{
    /**
     * Rules for creating an answer.
     */
    public function getCreateRules(): array
    {
        return [
            'question_id' => 'required|integer|exists:questions,id',
            'answers' => 'required|array|min:1',
            'answers.*.content' => 'required|string|max:255',
            'answers.*.is_correct' => 'sometimes|boolean',
        ];
    }

    /**
     * Rules for updating an answer.
     */
    public function getUpdateRules(): array
    {
        return [
            "question_id" => "sometimes|required|integer|exists:questions,id",
            "content" => "sometimes|required|string|max:255",
            "is_correct" => "sometimes|boolean",
        ];
    }

    /**
     * Get the validation rules for a collection of answer data.
     *
     * @return array
     */
    public function getDataCollectionRules(): array
    {
        return [
            "*.question_id" => "required|integer|exists:questions,id",
            "*.content" => "required|string|max:255",
            "*.is_correct" => "sometimes|boolean",
        ];
    }
}
