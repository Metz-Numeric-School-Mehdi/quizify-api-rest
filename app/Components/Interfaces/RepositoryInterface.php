<?php

namespace App\Components\Interfaces;

interface RepositoryInterface
{
    public function index();
    public function show(int $id);
    public function store(array $data);
    public function update(array $data, int $id);
    public function destroy($request, int $id);
}
