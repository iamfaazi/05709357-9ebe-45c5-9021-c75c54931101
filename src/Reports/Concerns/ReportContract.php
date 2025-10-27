<?php

namespace App\Reports\Concerns;

interface ReportContract
{
    /**
     * @param string $studentId
     * @return string|null
     */
    public function generate(string $studentId): ?string;
}