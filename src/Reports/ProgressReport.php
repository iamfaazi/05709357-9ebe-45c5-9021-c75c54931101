<?php

namespace App\Reports;

class ProgressReport extends Report
{
    /**
     * Generate progress report showing improvement over time
     * Handles multiple attempts of the same assessment
     *
     * @param mixed $studentId
     * @return string|null
     * @throws \Exception
     */
    public function generate($studentId): ?string
    {
        $this->loadReferenceData();

        // Took assumption as Most Recently completed Assessment Progress one.
        $mostRecentCompletedAssessment = $this->loader->getMostRecentCompleted($studentId);

        $allResponses = $this->loader->getResponseByStudentAssessment($studentId, $mostRecentCompletedAssessment->assessmentId);

        if (empty($allResponses)) {
            return "<e>Error: No completed assessments found for student ID: $studentId</e>\n";
        }

        // Group responses by attempt (each attempt has unique identifier)
        $attemptsByDate = $this->groupResponsesByAttempt($allResponses);

        // Calculate scores for each attempt
        $attemptScores = [];
        foreach ($attemptsByDate as $date => $attempt) {
            $correct = 0;
            $total = count($attempt['responses']);

            foreach ($attempt['responses'] as $response) {
                $question = $this->questions[$response->questionId] ?? null;
                if ($question && $response->response === $question->config->key) {
                    $correct++;
                }
            }

            $attemptScores[] = [
                'date' => $date,
                'correct' => $correct,
                'total' => $total,
                'attemptId' => $attempt['attemptId']
            ];
        }

        // Build report
        $report = $this->getStudentFullName($studentId);
        $report .= sprintf(
            " has completed %s assessment %d times in total. Date and raw score given below:\n\n",
            $this->getAssessmentName($studentId),
            count($attemptScores)
        );

        // List all attempts with scores
        foreach ($attemptScores as $score) {
            $report .= sprintf(
                "Date: %s, Raw Score: %d out of %d\n",
                $this->formatDate($score['date']),
                $score['correct'],
                $score['total']
            );
        }

        // Calculate improvement between oldest and most recent
        $oldest = reset($attemptScores);
        $newest = end($attemptScores);
        $improvement = $newest['correct'] - $oldest['correct'];

        $report .= "\n";
        $report .= sprintf(
            "%s got %d more correct in the recent completed assessment than the oldest\n",
            $this->getStudentFullName($studentId),
            $improvement
        );

        return $report;
    }

    /**
     * Group responses by attempt
     * Each attempt is identified by completed date + attempt number or unique attempt ID
     *
     * @param array $responses
     * @return array
     */
    private function groupResponsesByAttempt(array $responses): array
    {
        $attempts = [];

        foreach ($responses as $response) {
            // Create unique attempt key using completed date and attempt ID
            $attemptId = $response->attemptId ?? $response->id ?? uniqid();
            $completedDate = $response->completed ?? $response->started;


            //@TODO If multiple attempts on the same date, they'll have different attemptIds

            // Use completed date as primary grouping key
            $attemptKey = $completedDate;

            // If attempt with this date already exists, append attempt number
            if (!isset($attempts[$attemptKey])) {
                $attempts[$attemptKey] = [
                    'attemptId' => $attemptId,
                    'completed' => $completedDate,
                    'student' => $response->student,
                ];
            }

            $attempts[$attemptKey]['responses'] = $response->responses;
            $attempts[$attemptKey]['results'] = $response->results;
        }


        // Sort by date (oldest first)
        uksort($attempts, fn($a, $b) => strtotime(str_replace('/', '-', $a)) <=> strtotime(str_replace('/', '-', $b)));

        return $attempts;
    }
}