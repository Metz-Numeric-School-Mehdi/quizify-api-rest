<?php

namespace App\Repositories\Quiz;

interface RepositoryInterface
{
    public function index();
    public function show($id);
    public function store($request);
    public function update($request, $id);
    public function destroy($request, $id);
    public function submit($request, $quizId);
    public function storeAttempt($request, $quizId);
}
