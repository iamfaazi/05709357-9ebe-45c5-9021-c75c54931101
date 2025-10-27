<?php

namespace App\Reports\Concerns;

interface iReport
{
    public function generate(string $studentId): ?string;
}