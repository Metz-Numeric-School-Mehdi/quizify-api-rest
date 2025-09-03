<?php

namespace App\Http\Modules\Users\Strategies;

use App\Components\Abstracts\RuleStrategy;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * User validation rule strategy
 *
 * Defines validation rules for user operations including
 * profile updates, creation, and bulk operations
 */
class UserRuleStrategy extends RuleStrategy
{
    /**
     * Rules for creating a user
     *
     * @return array
     */
    public function getCreateRules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:users,username',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols()
            ],
            'role_id' => 'nullable|exists:roles,id',
            'profile_photo' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
            'avatar' => 'nullable|string|url|max:500',
            'team_id' => 'nullable|exists:teams,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
        ];
    }

    /**
     * Rules for updating a user
     *
     * @param int|null $id The user ID to ignore for uniqueness validation
     * @return array
     */
    public function getUpdateRules(?int $id = null): array
    {
        return [
            'username' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($id),
            ],
            'firstname' => 'sometimes|nullable|string|max:255',
            'lastname' => 'sometimes|nullable|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols()
            ],
            'role_id' => 'sometimes|nullable|exists:roles,id',
            'profile_photo' => 'sometimes|nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
            'avatar' => 'sometimes|nullable|string|url|max:500',
            'team_id' => 'sometimes|nullable|exists:teams,id',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
            'subscription_plan_id' => 'sometimes|nullable|exists:subscription_plans,id',
        ];
    }

    /**
     * Rules for profile update (user updating their own profile)
     *
     * @param int|null $id The user ID to ignore for uniqueness validation
     * @return array
     */
    public function getProfileUpdateRules(?int $id = null): array
    {
        return [
            'username' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($id),
            ],
            'firstname' => 'sometimes|nullable|string|max:255',
            'lastname' => 'sometimes|nullable|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)->letters()->numbers()->symbols()
            ],
            'profile_photo' => 'sometimes|nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
            'avatar' => 'sometimes|nullable|string|url|max:500',
            'team_id' => 'sometimes|nullable|exists:teams,id',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
        ];
    }

    /**
     * Get the validation rules for a collection of user data
     *
     * @return array
     */
    public function getDataCollectionRules(): array
    {
        return [
            '*.username' => 'required|string|max:255|unique:users,username',
            '*.firstname' => 'nullable|string|max:255',
            '*.lastname' => 'nullable|string|max:255',
            '*.email' => 'required|email|max:255|unique:users,email',
            '*.password' => [
                'required',
                Password::min(8)->letters()->numbers()->symbols()
            ],
            '*.role_id' => 'nullable|exists:roles,id',
            '*.avatar' => 'nullable|string|url|max:500',
            '*.team_id' => 'nullable|exists:teams,id',
            '*.organization_id' => 'nullable|exists:organizations,id',
            '*.subscription_plan_id' => 'nullable|exists:subscription_plans,id',
        ];
    }

    /**
     * Get validation messages in French
     *
     * @return array
     */
    public function getMessages(): array
    {
        return [
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 255 caractères.',
            'username.required' => 'Le nom d\'utilisateur est requis.',
            'firstname.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
            'lastname.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'email est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.required' => 'Le mot de passe est requis.',
            'profile_photo.image' => 'Le fichier doit être une image.',
            'profile_photo.max' => 'L\'image ne peut pas dépasser 2MB.',
            'profile_photo.mimes' => 'L\'image doit être au format jpeg, png, jpg ou gif.',
            'avatar.url' => 'L\'avatar doit être une URL valide.',
            'avatar.max' => 'L\'URL de l\'avatar ne peut pas dépasser 500 caractères.',
            'team_id.exists' => 'L\'équipe sélectionnée n\'existe pas.',
            'organization_id.exists' => 'L\'organisation sélectionnée n\'existe pas.',
            'subscription_plan_id.exists' => 'Le plan d\'abonnement sélectionné n\'existe pas.',
            'role_id.exists' => 'Le rôle sélectionné n\'existe pas.',
        ];
    }
}
