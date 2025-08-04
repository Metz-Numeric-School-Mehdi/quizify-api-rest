<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $ruleStrategy = app()->make($this->getRuleStrategy()::class);
        return $ruleStrategy->getRules();
    }

    /**
     * Get the RuleStrategy instance for the current entity.
     *
     * @return object
     */
    protected function getRuleStrategy()
    {
        $repositoryClass = class_basename($this->repository);
        $entity = str_replace("Repository", "", $repositoryClass);

        $plurals = [
            "Quiz" => "Quizzes",
            "User" => "Users",
            "Categorie" => "Categories",
        ];
        $plural = $plurals[$entity] ?? "{$entity}s";

        $strategyClass = "App\\Http\\Modules\\{$plural}\\Strategies\\{$entity}RuleStrategy";

        if (class_exists($strategyClass)) {
            return new $strategyClass();
        }

        throw new \Exception("Aucune RuleStrategy trouvée pour $entity ($strategyClass)");
    }
}
