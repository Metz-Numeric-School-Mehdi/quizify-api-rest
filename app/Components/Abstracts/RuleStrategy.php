<?php

namespace App\Components\Abstracts;

use App\Components\Interfaces\RuleStrategyInterface;

abstract class RuleStrategy implements RuleStrategyInterface
{
    /**
     * Return the minimal validation rules for the entity.
     */
    abstract public function getCreateRules(): array;

    /**
     * Return the full validation rules for the entity.
     */
    abstract public function getUpdateRules(): array;

    /**
     * Return the validation rules for a collection of entities.
     */
    abstract public function getDataCollectionRules(): array;
}
