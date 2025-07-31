<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Quiz\RepositoryInterface;

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

    public function store($request)
    {
        return $this->repository->store($request);
    }

    public function show($id)
    {
        return $this->repository->show($id);
    }

    public function update($request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($request, $id)
    {
        return $this->repository->destroy($request, $id);
    }
}
