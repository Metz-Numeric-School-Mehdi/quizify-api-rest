<?php
namespace App\Components;

use App\Repositories\Quiz\RepositoryInterface;
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
        return $this->model::create($data);
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

    public function storeAttempt($request, $quizId) {}
}
