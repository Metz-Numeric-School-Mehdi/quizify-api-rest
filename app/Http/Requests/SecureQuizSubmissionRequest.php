<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

/**
 * Requête de validation sécurisée pour la soumission de quiz
 *
 * Implémente les protections contre :
 * - A01:2021 Broken Access Control
 * - A03:2021 Injection
 * - Manipulation des réponses de quiz
 * - Soumission de données malveillantes
 */
class SecureQuizSubmissionRequest extends SecureFormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête
     *
     * @return bool
     */
    protected function authorizeSecure(): bool
    {
        $quiz = $this->route('quiz');

        if (!$quiz) {
            return false;
        }

        if (!Auth::check()) {
            return false;
        }

        if ($quiz->status !== 'published') {
            return false;
        }

        if ($quiz->is_private && !$this->userHasAccessToPrivateQuiz($quiz)) {
            return false;
        }

        return true;
    }

    /**
     * Règles de validation pour la soumission de quiz
     *
     * @return array
     */
    public function rules(): array
    {
        $quiz = $this->route('quiz');
        $securityRules = $this->getSecurityRules();
        $idRules = $this->getIdValidationRules();

        return [
            'responses' => ['required', 'array', 'min:1'],
            'responses.*.question_id' => [
                'required',
                'integer',
                'exists:questions,id',
                function ($attribute, $value, $fail) use ($quiz) {
                    if (!$this->questionBelongsToQuiz($value, $quiz->id)) {
                        $fail('La question spécifiée n\'appartient pas à ce quiz.');
                    }
                },
            ],
            'responses.*.answer_id' => [
                'required_without:responses.*.user_answer',
                'nullable',
                'integer',
                'exists:answers,id',
                function ($attribute, $value, $fail) {
                    if ($value && !$this->answerBelongsToQuestion($value, $this->getQuestionIdFromResponse($attribute))) {
                        $fail('La réponse spécifiée n\'appartient pas à cette question.');
                    }
                },
            ],
            'responses.*.user_answer' => [
                'required_without:responses.*.answer_id',
                'nullable',
                'string',
                'max:1000',
                $this->validateAgainstInjection('user_answer'),
            ],
            'start_time' => ['required', 'date', 'before_or_equal:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'time_spent' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Règles de sanitisation spécifiques pour les quiz
     *
     * @return array
     */
    protected function getSanitizationRules(): array
    {
        return [
            'responses.*.user_answer' => [
                'max_length' => 1000,
                'strip_tags' => true,
                'encode_html' => true,
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés
     *
     * @return array
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'responses.required' => 'Vous devez fournir au moins une réponse.',
            'responses.array' => 'Les réponses doivent être au format tableau.',
            'responses.*.question_id.required' => 'L\'ID de la question est obligatoire.',
            'responses.*.question_id.exists' => 'La question spécifiée n\'existe pas.',
            'responses.*.answer_id.exists' => 'La réponse spécifiée n\'existe pas.',
            'responses.*.user_answer.max' => 'La réponse ne peut pas dépasser 1000 caractères.',
            'start_time.required' => 'L\'heure de début est obligatoire.',
            'start_time.before_or_equal' => 'L\'heure de début ne peut pas être dans le futur.',
            'end_time.required' => 'L\'heure de fin est obligatoire.',
            'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'time_spent.required' => 'Le temps passé est obligatoire.',
            'time_spent.min' => 'Le temps passé doit être d\'au moins 1 seconde.',
        ]);
    }

    /**
     * Prépare les données pour validation avec contrôles de sécurité supplémentaires
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->validateQuizAccess();
        $this->validateSubmissionLimits();
        $this->deduplicateResponses();
    }

    /**
     * Vérifie si l'utilisateur a accès au quiz privé
     *
     * @param mixed $quiz
     * @return bool
     */
    private function userHasAccessToPrivateQuiz($quiz): bool
    {
        $user = Auth::user();

        if ($quiz->user_id === $user->id) {
            return true;
        }

        if ($user->organization_id && $quiz->organization_id === $user->organization_id) {
            return true;
        }

        if ($user->team_id && $quiz->team_id === $user->team_id) {
            return true;
        }

        return $quiz->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Vérifie si la question appartient au quiz
     *
     * @param int $questionId
     * @param int $quizId
     * @return bool
     */
    private function questionBelongsToQuiz(int $questionId, int $quizId): bool
    {
        return \App\Models\Question::where('id', $questionId)
            ->where('quiz_id', $quizId)
            ->exists();
    }

    /**
     * Vérifie si la réponse appartient à la question
     *
     * @param int $answerId
     * @param int $questionId
     * @return bool
     */
    private function answerBelongsToQuestion(int $answerId, int $questionId): bool
    {
        return \App\Models\Answer::where('id', $answerId)
            ->where('question_id', $questionId)
            ->exists();
    }

    /**
     * Extrait l'ID de la question depuis l'attribut de réponse
     *
     * @param string $attribute
     * @return int|null
     */
    private function getQuestionIdFromResponse(string $attribute): ?int
    {
        $parts = explode('.', $attribute);
        if (count($parts) >= 2) {
            $responseIndex = $parts[1];
            return $this->input("responses.{$responseIndex}.question_id");
        }
        return null;
    }

    /**
     * Valide l'accès au quiz
     *
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    private function validateQuizAccess(): void
    {
        $quiz = $this->route('quiz');
        $user = Auth::user();

        $existingAttempt = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->first();

        if ($existingAttempt && !$quiz->allow_multiple_attempts) {
            abort(403, 'Vous avez déjà complété ce quiz.');
        }
    }

    /**
     * Valide les limites de soumission
     *
     * @return void
     */
    private function validateSubmissionLimits(): void
    {
        $user = Auth::user();
        $recentSubmissions = \App\Models\QuizAttempt::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentSubmissions >= 10) {
            abort(429, 'Trop de soumissions récentes. Veuillez patienter.');
        }
    }

    /**
     * Supprime les réponses dupliquées pour la même question
     *
     * @return void
     */
    private function deduplicateResponses(): void
    {
        $responses = $this->input('responses', []);
        $seen = [];
        $filtered = [];

        foreach ($responses as $response) {
            $questionId = $response['question_id'] ?? null;
            if ($questionId && !isset($seen[$questionId])) {
                $seen[$questionId] = true;
                $filtered[] = $response;
            }
        }

        $this->merge(['responses' => $filtered]);
    }

    /**
     * Obtient les données validées avec contrôles de sécurité supplémentaires
     *
     * @param array|string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated) && isset($validated['responses'])) {
            $validated['responses'] = $this->validateResponseSecurity($validated['responses']);
        }

        return $validated;
    }

    /**
     * Valide la sécurité des réponses soumises
     *
     * @param array $responses
     * @return array
     */
    private function validateResponseSecurity(array $responses): array
    {
        foreach ($responses as &$response) {
            if (isset($response['user_answer'])) {
                $response['user_answer'] = $this->sanitizer->sanitizeValue(
                    $response['user_answer'],
                    'user_answer'
                );
            }
        }

        return $responses;
    }
}
