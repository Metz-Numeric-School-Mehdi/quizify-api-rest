<?php

namespace App\Components\Interfaces;

interface RuleStrategyInterface
{
    /**
     * Return the minimal validation rules for the entity.
     *
     * @return array
     */
    public function getCreateRules(): array;

    /**
     * Return the full validation rules for the entity.
     *
     * @return array
     */
    public function getUpdateRules(): array;

    /**
     * Return the validation rules for a collection of entities.
     *
     * @return array
     */
    public function getDataCollectionRules(): array;
}
