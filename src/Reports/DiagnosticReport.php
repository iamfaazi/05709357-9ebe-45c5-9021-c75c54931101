<?php

namespace App\Reports;

class DiagnosticReport extends Report
{

    /**
     * @throws \Exception
     */
    public function generate($studentId): ?string
    {
        $this->loadReferenceData();

        // Get ALL completed responses
        $allResponses = $this->loader->getStudentResponse($studentId);

        if (empty($allResponses)) {
            return "<error>Error: No completed assessments found for student ID: $studentId\n<error>";
        }



        // Calculate aggregated results from ALL responses
        $strandResults = $this->calculateAggregatedStrandResults($allResponses);

        $totalCorrect = array_sum(array_column($strandResults, 'correct'));
        $totalQuestions = array_sum(array_column($strandResults, 'total'));

        // Build report
        // Get header with most recent assessment info
        $report = $this->getRecentAssessmentHeader($studentId);
        $report .= "He got $totalCorrect questions right out of $totalQuestions. ";
        $report .= "Details by strand given below:\n\n";

        foreach ($strandResults as $strand => $stats) {
            $report .= sprintf(
                "%s: %d out of %d correct\n",
                $strand,
                $stats['correct'],
                $stats['total']
            );
        }

        return $report;
    }
}