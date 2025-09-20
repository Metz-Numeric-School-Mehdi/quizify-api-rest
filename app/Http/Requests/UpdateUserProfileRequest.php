<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

/**
 * Request validation for updating user profile
 *
 * Handles validation and sanitization for user profile updates
 * including optional password changes and file uploads
 */
class UpdateUserProfileRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user() ? $this->user()->id : null;

        return [
            'username' => 'sometimes|string|max:255|unique:users,username,' . $userId,
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $userId,
            'password' => ['sometimes', 'confirmed', Password::min(8)->letters()->numbers()->symbols()],
            'profile_photo' => 'sometimes|image|max:2048|mimes:jpeg,png,jpg,gif',
            'avatar' => 'sometimes|string|url|max:500',
            'team_id' => 'sometimes|nullable|exists:teams,id',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
        ];
    }

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.unique' => 'Ce nom d\'utilisateur est déjà pris.',
            'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 255 caractères.',
            'firstname.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
            'lastname.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'profile_photo.image' => 'Le fichier doit être une image.',
            'profile_photo.max' => 'L\'image ne peut pas dépasser 2MB.',
            'profile_photo.mimes' => 'L\'image doit être au format jpeg, png, jpg ou gif.',
            'avatar.url' => 'L\'avatar doit être une URL valide.',
            'avatar.max' => 'L\'URL de l\'avatar ne peut pas dépasser 500 caractères.',
            'team_id.exists' => 'L\'équipe sélectionnée n\'existe pas.',
            'organization_id.exists' => 'L\'organisation sélectionnée n\'existe pas.',
        ];
    }

    /**
     * Get sanitization rules for input cleaning
     *
     * @return array<string, string>
     */
    protected function getSanitizationRules(): array
    {
        return [
            'username' => 'string|trim|lowercase',
            'firstname' => 'string|trim|title_case',
            'lastname' => 'string|trim|title_case',
            'email' => 'email|trim|lowercase',
            'avatar' => 'url|trim',
        ];
    }

    /**
     * Handle a failed validation attempt
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'message' => 'Erreur de validation des données.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
