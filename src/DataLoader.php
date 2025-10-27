<?php

namespace App;

use App\Infrastructure\JsonStreamReader;

class DataLoader
{
    const string STUDENT_DATASOURCE = 'students.json';

    const string ASSESSMENT_DATASOURCE = 'assessments.json';

    const string QUESTIONS_DATASOURCE = 'questions.json';

    const string STUDENT_RESPONSES_DATASOURCE = 'student-responses.json';

    const string DEFAULT_DATASOURCE_PATH = './data/';


    private static ?self $instance = null;


    /**
     * @param string $dataPath
     */
    public function __construct(private readonly string $dataPath = self::DEFAULT_DATASOURCE_PATH)
    {
        // Validate data path exists and is readable
        if (!is_dir($dataPath)) {
            throw new \RuntimeException("Data directory does not exist: {$dataPath}");
        }

        if (!is_readable($dataPath)) {
            throw new \RuntimeException("Data directory is not readable: {$dataPath}");
        }

    }

    /**
     * Singleton accessor method
     * @param string $dataDir
     * @return DataLoader
     */
    public static function getInstance(string $dataDir = self::DEFAULT_DATASOURCE_PATH): self
    {
        if (self::$instance === null) {
            self::$instance = new self($dataDir);
        }

        return self::$instance;
    }


    /**
     * @throws \Exception
     */
    public function loadStudents(): array
    {
        $students = [];
        $reader = new JsonStreamReader($this->dataPath . self::STUDENT_DATASOURCE);

        foreach ($reader->parse() as $student) {
            $students[$student->id] = $student;
        }
        return $students;
    }

    /**
     * @throws \Exception
     */
    public function loadAssessments(): array
    {
        $assessments = [];
        $reader = new JsonStreamReader($this->dataPath . self::ASSESSMENT_DATASOURCE);

        foreach ($reader->parse() as $assessment) {
            $assessments[$assessment->id] = $assessment;
        }
        return $assessments;
    }

    /**
     * @throws \Exception
     */
    public function loadQuestions(): array
    {
        $questions = [];
        $reader = new JsonStreamReader($this->dataPath . self::QUESTIONS_DATASOURCE);

        foreach ($reader->parse() as $question) {
            $questions[$question->id] = $question;
        }
        return $questions;
    }

    /**
     * @throws \Exception
     */
    /**
     * Get ALL completed responses for a student
     * (Student can have multiple responses -Returns array of all completed assessments)
     * @throws \Exception
     */
    public function getStudentResponse($studentId): array
    {
        $reader = new JsonStreamReader($this->dataPath . self::STUDENT_RESPONSES_DATASOURCE);
        $responses = [];

        foreach ($reader->parse() as $response) {
            // Only include COMPLETED responses
            if (isset($response?->student?->id) &&
                $response->student->id === $studentId) { // Must have 'completed' key  isset($response['completed'])
                $responses[] = $response;
            }
        }

        return $responses;
    }


    /**
     * Get the most recently completed response for display info
     * Returns null if no completed assessments
     * @throws \Exception
     */
    public function getMostRecentCompleted($studentId)
    {
        $reader = new JsonStreamReader($this->dataPath . self::STUDENT_RESPONSES_DATASOURCE);

        $mostRecent = null;
        $mostRecentDate = null;

        foreach ($reader->parse() as $response) {
            if (isset($response?->student?->id) &&
                $response->student->id === $studentId &&
                isset($response->completed)) {

                $completedDate = \DateTime::createFromFormat('d/m/Y H:i:s', $response->completed);

                if ($mostRecentDate === null || $completedDate > $mostRecentDate) {
                    $mostRecentDate = $completedDate;
                    $mostRecent = $response;
                }
            }
        }

        return $mostRecent;
    }



    /**
     * @throws \Exception
     */
    /**
     * Get ALL completed responses for a Student Assessment
     * (Student can have multiple responses -Returns array of all completed assessments)
     * @throws \Exception
     */
    public function getResponseByStudentAssessment($studentId, $assessmentId): array
    {
        $reader = new JsonStreamReader($this->dataPath . self::STUDENT_RESPONSES_DATASOURCE);
        $responses = [];

        foreach ($reader->parse() as $response) {
            if (isset($response?->student?->id) &&
                $response->student->id === $studentId && $response->assessmentId === $assessmentId) {
                $responses[] = $response;
            }
        }

        return $responses;
    }
}