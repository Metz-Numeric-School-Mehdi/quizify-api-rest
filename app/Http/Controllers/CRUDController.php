<?php
namespace App\Http\Controllers;

use App\Components\Interfaces\RepositoryInterface;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Display a listing of the entities.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json($this->repository->index());
    }

    /**
     * Store a newly created entity in storage.
     *
     * Validates the incoming request using the appropriate RuleStrategy,
     * attempts to store the entity via the repository, and returns a JSON response.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $ruleStrategy = $this->getRuleStrategy();
        $validator = Validator::make($request->all(), $ruleStrategy->getCreateRules());

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

    /**
     * Display the specified entity.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $entity = $this->repository->show($id);
            return response()->json($entity);
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    "message" => __("crud.not_found"),
                ],
                404,
            );
        } catch (ApiException $e) {
            return response()->json(
                [
                    "message" => __("crud.unknown_error"),
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Update the specified entity in storage.
     *
     * @param  mixed  $request
     * @param  int  $id
     * @return mixed
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $ruleStrategy = $this->getRuleStrategy();
        $validator = Validator::make($request->all(), $ruleStrategy->getUpdateRules());

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
            $entity = $this->repository->update($validated, $id);
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
        $key = "crud.updated_successfully_" . $entityInfo["gender"];

        return response()->json(
            [
                "message" => __($key, ["Entity" => $entityInfo["label"]]),
                "data" => $entity,
            ],
            201,
        );
    }

    /**
     * Remove the specified entity from storage.
     *
     * Attempts to delete the entity via the repository and returns a JSON response.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $entity = $this->repository->destroy($request, $id);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => __("crud.not_found"),
                ],
                404,
            );
        }

        $entityInfo = $this->getEntityLabelAndGender();
        $key = "crud.deleted_successfully_" . $entityInfo["gender"];

        return response()->json(
            [
                "message" => __($key, ["Entity" => $entityInfo["label"]]),
            ],
            200,
        );
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
            "question" => ["label" => "Question", "gender" => "f"],
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
            "Question" => "Questions",
        ];
        $plural = $plurals[$entity] ?? "{$entity}s";

        $strategyClass = "App\\Http\\Modules\\{$plural}\\Strategies\\{$entity}RuleStrategy";

        if (class_exists($strategyClass)) {
            return new $strategyClass();
        }

        throw new \Exception("Aucune RuleStrategy trouvée pour $entity ($strategyClass)");
    }
}
