<?php

namespace App\Repositories\Concerns;

interface RepositoryInterface
{
    /**
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array;
}