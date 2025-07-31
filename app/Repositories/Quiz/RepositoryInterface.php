<?php

namespace App\Repositories\Quiz;

interface RepositoryInterface
{
    public function index();
    public function submit($request, $quizId);
    public function store($request);
    public function show($id);
    public function update($request, $id);
    public function destroy($request, $id);
    public function storeAttempt($request, $quizId);
}
