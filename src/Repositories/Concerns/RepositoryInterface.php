<?php

namespace App\Repositories\Concerns;

interface RepositoryInterface
{
    public function findById(string $id): ?array;
}