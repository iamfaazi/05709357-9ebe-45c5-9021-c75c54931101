<?php

namespace App\Reports;

use App\DataLoader;
use JetBrains\PhpStorm\NoReturn;
use App\Reports\Concerns\ReportContract;

abstract class AbstractReportGenerator implements ReportContract
{
    protected mixed $students;
    protected mixed $assessments;
    protected mixed $questions;
    protected mixed $loader;

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
     * Helper: Format date with ordinal suffix (e.g., 1st January 2025 3:45 PM)
     */
    protected function formatDate($dateString): string
    {
        $date = \DateTime::createFromFormat('d/m/Y H:i:s', $dateString);
        if (!$date) {
            return $dateString;
        }

        $day = (int)$date->format('j');
        $suffix = match (true) {
            in_array($day % 100, [11, 12, 13]) => 'th',
            $day % 10 === 1 => 'st',
            $day % 10 === 2 => 'nd',
            $day % 10 === 3 => 'rd',
            default => 'th',
        };

        return sprintf('%d%s%s', $day, $suffix, $date->format(' F Y g:i A'));
    }

    /**
     * Helper: Calculate strand results from ALL responses
     * @param $allResponses
     * @return array
     */
    protected function calculateAggregatedStrandResults($allResponses): array
    {
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

    /**
     * @TODO to be removed
     * @param $output
     * @return void
     */
    #[NoReturn]
    public function dd(...$output)
    {
        foreach ($output as $line) {
            echo "\n";
            print_r($line);
            echo "\n\n";
        }

        die();
    }

    /**
     * Get assessment name from the assessment data
     *
     * @param $studentId
     * @return string
     * @throws \Exception
     */
    protected function getAssessmentName($studentId): string
    {
        // Get the most recent assessment to extract the name
        if (!empty($this->assessments)) {
            $mostRecentAssessment = $this->loader->getMostRecentCompleted($studentId);
            return $this->assessments[$mostRecentAssessment->assessmentId]->name ?? 'Assessment';
        }

        return 'Assessment';
    }

    /**
     * Get student's first name
     *
     * @param mixed $studentId
     * @return string
     */
    protected function getStudentFullName(?string $studentId): string
    {
        $student = $this->students[$studentId] ?? null;

        if (!$student) {
            return "Student";
        }

        return $student?->firstName . ' ' . $student?->lastName;
    }
}