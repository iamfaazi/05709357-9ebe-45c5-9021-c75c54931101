<?php

namespace App\Reports;

use App\DataLoader;
use App\Reports\Concerns\iReport;

abstract class Report implements iReport
{
    protected $students;
    protected $assessments;
    protected $questions;
    protected $loader;

    public function __construct()
    {
        $this->loader = DataLoader::getInstance();
    }

    /**
     * Load reference data
     * @throws \Exception
     */
    protected function loadReferenceData(): void
    {
        $this->students = $this->loader->loadStudents();
        $this->assessments = $this->loader->loadAssessments();
        $this->questions = $this->loader->loadQuestions();
    }

    /**
     * Abstract method - each report type implements this
     */
    abstract public function generate($studentId): ?string;

    /**
     * Helper: Format date
     */
    protected function formatDate($dateString)
    {
        $date = \DateTime::createFromFormat('d/m/Y H:i:s', $dateString);
        if (!$date) return $dateString;

        $day = $date->format('j');
        $suffix = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        if ((($day % 100) >= 11) && (($day % 100) <= 13)) {
            $suffix = 'th';
        } else {
            $suffix = $suffix[$day % 10];
        }

        return $day . $suffix . $date->format(' F Y g:i A');
    }

    /**
     * Helper: Calculate strand results from ALL responses
     */
    protected function calculateAggregatedStrandResults($allResponses) {
        $results = [];

        foreach ($allResponses as $studentResponse) {
            foreach ($studentResponse->responses as $response) {
                $questionId = $response->questionId;
                $studentAnswer = $response->response;

                if (!isset($this->questions[$questionId])) {
                    continue;
                }

                $question = $this->questions[$questionId];
                $strand = $question->strand;
                $correctAnswer = $question->config->key;

                if (!isset($results[$strand])) {
                    $results[$strand] = ['correct' => 0, 'total' => 0];
                }

                $results[$strand]['total']++;

                if ($studentAnswer === $correctAnswer) {
                    $results[$strand]['correct']++;
                }
            }
        }

        ksort($results);
        return $results;
    }

    /**
     * Helper: Get header text with most recent assessment info
     * @throws \Exception
     */
    protected function getRecentAssessmentHeader($studentId): string
    {
        $mostRecent = $this->loader->getMostRecentCompleted($studentId);

        if (!$mostRecent) {
            return "No completed assessments found for this student.\n";
        }

        $student = $this->students[$mostRecent->student->id];
        $assessment = $this->assessments[$mostRecent->assessmentId];

        $studentName = $student->firstName . ' ' . $student->lastName;
        $assessmentName = $assessment->name;
        $completedDate = $this->formatDate($mostRecent->completed);

        return "$studentName recently completed $assessmentName assessment on $completedDate\n";
    }
}