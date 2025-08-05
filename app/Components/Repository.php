<?php
namespace App\Components;

use App\Components\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface
{
    /**
     * The Eloquent model instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * Repository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        return $this->model->all();
    }

    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Store a newly created model in storage.
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        \Log::info('Début création quiz', ['data' => $data]);

        try {
            $quiz = $this->model::create($data);
            \Log::info('Quiz créé avec succès', ['quiz_id' => $quiz->id]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du quiz', ['error' => $e->getMessage()]);
            throw $e; // ou gérer l'erreur comme tu veux
        }

        try {
            \Log::info('Début indexation Elasticsearch pour quiz_id ' . $quiz->id);
            $quiz->searchable();
            \Log::info('Indexation Elasticsearch réussie pour quiz_id ' . $quiz->id);
        } catch (\Exception $e) {
            \Log::error('Erreur d’indexation Elasticsearch', [
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Pas de throw ici si tu veux continuer malgré l'erreur d'indexation
        }

        \Log::info('Fin méthode store pour quiz_id ' . $quiz->id);

        return $quiz;
    }


    /**
     * Update the specified model in storage.
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update($data, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);
        return $model;
    }

    /**
     * Remove the specified model from storage.
     *
     * @param mixed $request
     * @param int $id
     * @return Model
     */
    public function destroy($request, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->delete();
        return $model;
    }

    public function storeAttempt($request, $quizId)
    {
    }
}
