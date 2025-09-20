<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InputSanitizationService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Classe de base pour les requêtes avec validation et sanitisation sécurisées
 *
 * Implémente les protections contre :
 * - A03:2021 Injection
 * - A01:2021 Broken Access Control
 * - Validation insuffisante des entrées
 * - Bypass des contrôles de sécurité
 */
abstract class SecureFormRequest extends FormRequest
{
    /**
     * Service de sanitisation des entrées
     */
    protected InputSanitizationService $sanitizer;

    /**
     * Constructeur avec injection du service de sanitisation
     */
    public function __construct()
    {
        parent::__construct();
        $this->sanitizer = app(InputSanitizationService::class);
    }

    /**
     * Prépare les données pour validation en les sanitisant
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $sanitized = $this->sanitizer->sanitizeAndValidate(
            $this->all(),
            $this->getSanitizationRules()
        );

        $this->replace($sanitized);
    }

    /**
     * Règles de sanitisation spécifiques (à surcharger dans les classes enfants)
     *
     * @return array
     */
    protected function getSanitizationRules(): array
    {
        return [];
    }

    /**
     * Messages d'erreur personnalisés sécurisés
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
            'unique' => 'Cette valeur pour :attribute existe déjà.',
            'min' => 'Le champ :attribute doit contenir au moins :min caractères.',
            'max' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
            'regex' => 'Le format du champ :attribute est invalide.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'in' => 'La valeur sélectionnée pour :attribute est invalide.',
            'boolean' => 'Le champ :attribute doit être vrai ou faux.',
            'integer' => 'Le champ :attribute doit être un nombre entier.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'array' => 'Le champ :attribute doit être un tableau.',
            'file' => 'Le champ :attribute doit être un fichier.',
            'image' => 'Le champ :attribute doit être une image.',
            'mimes' => 'Le fichier :attribute doit être de type: :values.',
            'max_file_size' => 'Le fichier :attribute ne peut pas dépasser :max KB.',
        ];
    }

    /**
     * Attributs personnalisés pour les messages d'erreur
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'email' => 'adresse email',
            'password' => 'mot de passe',
            'password_confirmation' => 'confirmation du mot de passe',
            'name' => 'nom',
            'title' => 'titre',
            'description' => 'description',
            'content' => 'contenu',
            'username' => "nom d'utilisateur",
            'first_name' => 'prénom',
            'last_name' => 'nom de famille',
            'phone' => 'téléphone',
        ];
    }

    /**
     * Gère les échecs de validation avec réponse sécurisée
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $this->formatValidationErrors($validator->errors()->toArray());

        throw new HttpResponseException(
            response()->json([
                'error' => 'Validation Failed',
                'message' => 'Les données fournies ne sont pas valides.',
                'errors' => $errors,
                'code' => 422,
            ], 422)
        );
    }

    /**
     * Formate les erreurs de validation de manière sécurisée
     *
     * @param array $errors
     * @return array
     */
    private function formatValidationErrors(array $errors): array
    {
        $formatted = [];

        foreach ($errors as $field => $messages) {
            $formatted[$field] = [
                'messages' => $messages,
                'first' => $messages[0] ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Règles de validation communes pour la sécurité
     *
     * @return array
     */
    protected function getSecurityRules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'unique:users,username',
            ],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZÀ-ÿ\s\'-]+$/'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\-\(\)\s]+$/'],
        ];
    }

    /**
     * Valide les fichiers uploadés de manière sécurisée
     *
     * @return array
     */
    protected function getFileValidationRules(): array
    {
        $maxSize = config('security.file_upload.max_file_size', 2048);
        $allowedMimes = implode(',', config('security.file_upload.allowed_mime_types', ['jpeg', 'png', 'pdf']));

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxSize}",
                "mimes:{$allowedMimes}",
            ],
            'image' => [
                'required',
                'image',
                "max:{$maxSize}",
                'mimes:jpeg,png,gif',
                'dimensions:min_width=50,min_height=50,max_width=2000,max_height=2000',
            ],
        ];
    }

    /**
     * Valide les entrées contre les injections connues
     *
     * @param string $field
     * @return string
     */
    protected function validateAgainstInjection(string $field): string
    {
        return "regex:/^(?!.*(<script|javascript:|vbscript:|onload=|onerror=|onclick=|eval\(|exec\(|union\s+select|insert\s+into|update\s+set|delete\s+from)).*$/i";
    }

    /**
     * Règles de validation pour les IDs et clés étrangères
     *
     * @return array
     */
    protected function getIdValidationRules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ];
    }

    /**
     * Valide que l'utilisateur a l'autorisation d'accéder à la ressource
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->authorizeSecure();
    }

    /**
     * Autorisation sécurisée (à surcharger dans les classes enfants)
     *
     * @return bool
     */
    protected function authorizeSecure(): bool
    {
        return true;
    }

    /**
     * Obtient les données validées avec nettoyage supplémentaire
     *
     * @param array|string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated)) {
            return $this->sanitizer->sanitizeAndValidate($validated);
        }

        return $validated;
    }
}
