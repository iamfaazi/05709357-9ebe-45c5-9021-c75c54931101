<?php

namespace App\Factories;


use App\Reports\AbstractReportGenerator;
use App\Reports\FeedbackReport;
use App\Reports\ProgressReport;
use App\Reports\DiagnosticReport;

/**
 * Factory Pattern
 * Creates report generators
 */
class ReportGeneratorFactory
{


    /**
     * Create report based on type
     *
     * @param string $type Report type: 'diagnostic', 'progress', 'feedback'
     * @return AbstractReportGenerator
     * @throws \Exception
     */
    public static function createReport(string $type): AbstractReportGenerator
    {
        return match (strtolower($type)) {
            'diagnostic' => new DiagnosticReport(),
            'progress' => new ProgressReport(),
            'feedback' => new FeedbackReport(),
            default => throw new \Exception("Unknown report type: $type"),
        };
    }

    /**
     * Get list of available report types
     */
    public static function getAvailableTypes(): array
    {
        return [
            'diagnostic' => 'Diagnostic Report - Performance by strand',
            'progress' => 'Progress Report - Overall score with percentages',
            'feedback' => 'Feedback Report - Hints for incorrect answers',
        ];
    }
}