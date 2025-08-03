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

    public function store($request)
    {
        $data = method_exists($request, "validated") ? $request->validated() : $request->all();
        return $this->model::create($data);
    }

    public function submit($request, $quizId) {}

    public function update($request, $id) {}
    public function destroy($request, $id) {}
    public function storeAttempt($request, $quizId) {}

    // protected function applyFilter(array $params)
    // {
    //     $query = $this->model->newQuery();
    // }

    // protected function setFilters(array &$params, Builder $query): void
    // {
    //     if (isset($params["filters"])) {
    //         foreach ($params["filters"] as $key => $value) {
    //             if (is_array($value)) {
    //                 $query->whereIn($key, $value);
    //             } else {
    //                 $query->where($key, $value);
    //             }
    //         }
    //         unset($params["filters"]);
    //     }
    // }
}
