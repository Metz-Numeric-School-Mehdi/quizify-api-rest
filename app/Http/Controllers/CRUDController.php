<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Quiz\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        $ruleStrategy = $this->getRuleStrategy();
        $validator = Validator::make($request->all(), $ruleStrategy->getRules());

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => __("crud.validation_error"),
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        $validated = $validator->validated();

        try {
            $entity = $this->repository->store($validated);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => __("crud.creation_error"),
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }

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
