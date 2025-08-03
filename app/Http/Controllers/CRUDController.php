<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Quiz\RepositoryInterface;
use Illuminate\Http\Request;

class CRUDController extends Controller
{
    /**
     * The repository instance.
     *
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * CRUDController constructor.
     *
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        return response()->json($this->repository->index());
    }

    public function store(Request $request)
    {
        $entity = $this->repository->store($request);
        if ($entity) {
            $entityInfo = $this->getEntityLabelAndGender();
            $key = "crud.created_successfully_" . $entityInfo["gender"];
            return response()->json(
                [
                    "message" => __($key, ["Entity" => $entityInfo["label"]]),
                    "data" => $entity,
                ],
                201,
            );
        }
    }

    public function show($id)
    {
        return $this->repository->show($id);
    }

    public function update($request, $id)
    {
        return $this->repository->update($request, $id);
    }

    /**
     * Get the display label and gender for the current entity.
     *
     * @return array
     */
    protected function getEntityLabelAndGender()
    {
        $class = class_basename($this->repository);
        $entity = strtolower(str_replace("Repository", "", $class));

        $labels = [
            "quiz" => ["label" => "Quiz", "gender" => "m"],
            "user" => ["label" => "Utilisateur", "gender" => "m"],
            "categorie" => ["label" => "Catégorie", "gender" => "f"],
        ];

        $default = ["label" => ucfirst($entity), "gender" => "m"];
        return $labels[$entity] ?? $default;
    }
}
