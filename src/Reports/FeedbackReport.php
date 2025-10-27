<?php

namespace App\Reports;

class FeedbackReport extends Report
{
    /**
     * Generate detailed feedback report for most recent assessment attempt
     * Shows incorrect answers with correct solutions and educational feedback
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

        if (empty($mostRecentCompletedAssessment)) {
            return "<e>Error: No completed assessments found for student ID: $studentId</e>\n";
        }

        $recentResponses = $mostRecentCompletedAssessment->responses;
        $attemptDate = $mostRecentCompletedAssessment->completed;

        // Analyze responses from the most recent attempt
        $incorrectQuestions = [];
        $correctCount = 0;
        $totalCount = count($recentResponses);

        foreach ($recentResponses as $response) {
            $question = $this->questions[$response->questionId] ?? null;

            if (!$question) {
                continue;
            }

            $correctAnswer = $question->config->key;
            $studentAnswer = $response->response;

            if ($this->isCorrectAnswer($studentAnswer, $correctAnswer)) {
                $correctCount++;
            } else {
                //Pick the Correct Answer value
                foreach ($question->config->options as $option) {
                    if ($option->id === $question->config->key) {
                        $correctAnswer = sprintf("%s with value %s", $option->label, $option->value);
                    }

                    if ($option->id === $response->response) {
                        $studentAnswer = sprintf("%s with value %s", $option->label, $option->value);
                    }
                }

                $incorrectQuestions[] = [
                    'question' => $question,
                    'studentAnswer' => $studentAnswer,
                    'correctAnswer' => $correctAnswer,
                    'response' => $response
                ];
            }
        }


        // Build report header
        $report = $this->buildReportHeader($studentId, $attemptDate, $correctCount, $totalCount);

        // If all correct, congratulate and exit
        if (empty($incorrectQuestions)) {
            $report .= "\nExcellent work! All questions were answered correctly.\n";
            $report .= "Keep up the great work!\n";
            return $report;
        }

        // Sort questions by position
        usort($incorrectQuestions, fn($a, $b) => ($a['question']['position'] ?? 0) <=> ($b['question']['position'] ?? 0));

        $questionNumber = 1;
        foreach ($incorrectQuestions as $question) {

            $report .= $this->formatQuestionFeedback(
                $questionNumber,
                $question['question'],
                $question['studentAnswer'],
                $question['correctAnswer']
            );
            $questionNumber++;
        }

        $report .= "\n";


        return $report;
    }

    /**
     * Build report header with student info and score summary
     * @param $studentId
     * @param $attemptDate
     * @param $correct
     * @param $total
     * @return string
     * @throws \Exception
     */
    private function buildReportHeader($studentId, $attemptDate, $correct, $total): string
    {
        $report = $this->getStudentFullName($studentId);
        $report .= sprintf(
            " recently completed %s assessment on %s \n",
            $this->getAssessmentName($studentId),
            $this->formatDate($attemptDate)
        );

        $report .= sprintf(
            "He got %d questions right out of %d. Feedback for wrong answers given below\n",
            $correct,
            $total
        );

        return $report;
    }


    /**
     * Format detailed feedback for a single question
     * @param int $number
     * @param $question
     * @param $studentAnswer
     * @param $correctAnswer
     * @return string
     */
    private function formatQuestionFeedback(
        int $number,
            $question,
            $studentAnswer,
            $correctAnswer
    ): string
    {

        /**
         * Format of Output
         *
         * Question: What is the 'median' of the following group of numbers 5, 21, 7, 18, 9?
         * Your answer: A with value 7
         * Right answer: B with value 9
         * Hint: You must first arrange the numbers in ascending order. The median is the middle term, which in this case is 9
         */

        $feedback = "\n";
        $feedback .= sprintf("Question: %s \n", $question->stem);


        // Show answer comparison
        $feedback .= sprintf("Your answer:    %s\n", $this->formatAnswer($studentAnswer));
        $feedback .= sprintf("Correct answer: %s\n", $this->formatAnswer($correctAnswer));

        // Add explanation/feedback if available
        if (!empty($question->config->hint)) {
            $feedback .= "Hint: {$question->config->hint} \n";
        }

        $feedback .= "\n";
        return $feedback;
    }


    /**
     * Format answer for display
     */
    private function formatAnswer($answer): string
    {
        if ($answer === null || $answer === '') {
            return '(No answer provided)';
        }

        if (is_array($answer)) {
            return implode(', ', array_map(fn($a) => $this->formatAnswer($a), $answer));
        }

        if (is_bool($answer)) {
            return $answer ? 'True' : 'False';
        }

        return (string)$answer;
    }

    /**
     * Check if student answer matches correct answer
     * Handles various data types and formats
     */
    private function isCorrectAnswer($studentAnswer, $correctAnswer): bool
    {
        // Strictly match
        if ($studentAnswer === $correctAnswer) {
            return true;
        }

        // Case-insensitive string comparison
        if (is_string($studentAnswer) && is_string($correctAnswer)) {
            return strcasecmp(trim($studentAnswer), trim($correctAnswer)) === 0;
        }

        // Numeric comparison
        if (is_numeric($studentAnswer) && is_numeric($correctAnswer)) {
            return (float)$studentAnswer === (float)$correctAnswer;
        }

        // Array comparison
        if (is_array($studentAnswer) && is_array($correctAnswer)) {
            sort($studentAnswer);
            sort($correctAnswer);
            return $studentAnswer === $correctAnswer;
        }

        return false;
    }
}