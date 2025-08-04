<?php

namespace App\Components\Abstracts;

use App\Components\Interfaces\RuleStrategyInterface;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\ApiException;

abstract class RuleStrategy implements RuleStrategyInterface
{
    /**
     * Return the minimal validation rules for the entity.
     */
    abstract public function getPrimitiveRules(): array;

    /**
     * Return the full validation rules for the entity.
     */
    abstract public function getRules(): array;

    /**
     * Return the validation rules for a collection of entities.
     */
    abstract public function getDataCollectionRules(): array;

    /**
     * Validate data for store (creation).
     */
    public function validateStore(array $params): array
    {
        $validator = Validator::make($params, $this->getRules());

        if ($validator->fails()) {
            throw new ApiException(
                "Validation failed",
                $validator->errors()->toArray(),
                "VALIDATION_ERROR",
            );
        }

        return $validator->validated();
    }

    /**
     * Validate data for update (partial update).
     */
    public function validateUpdate(array $params): array
    {
        $validator = Validator::make($params, $this->getPrimitiveRules());

        if ($validator->fails()) {
            throw new ApiException(
                "Validation failed",
                $validator->errors()->toArray(),
                "VALIDATION_ERROR",
            );
        }

        return $validator->validated();
    }
}
