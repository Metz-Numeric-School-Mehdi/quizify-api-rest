<?php

namespace App\Repositories\Quiz;

interface RepositoryInterface
{
    public function index();
    public function show(int $id);
    public function store(array $data);
    public function update(array $data, int $id);
    public function destroy($request, int $id);
    public function submit($request, int $quizId);
    public function storeAttempt($request, int $quizId);
}
