<?php

namespace Tests\Reports;

use App\DataLoader;
use App\Reports\ProgressReport;
use PHPUnit\Framework\TestCase;

class ProgressReportTest extends TestCase
{
    private ProgressReport $report;
    private DataLoader $mockLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->students = [
            'student1' => (object)[
                'id' => 'student1',
                'firstName' => 'Tony',
                'lastName' => 'Stark',
                'yearLevel' => 6
            ]
        ];

        $this->assessments = [
            (object)[
                'id' => 'assessment1',
                'name' => 'Numeracy',
                'questions' => []
            ]
        ];

        $this->questions = [];

        $this->responses = [
            (object)[
                'id' => 'response1',
                'assessmentId' => 'assessment1',
                'assigned' => '14/12/2019 10:31:00',
                'started' => '16/12/2019 10:00:00',
                'completed' => '16/12/2019 10:46:00',
                'student' => (object)['id' => 'student1', 'yearLevel' => 3],
                'responses' => [
                    (object)['questionId' => 'numeracy1', 'response' => 'option2'],
                    (object)['questionId' => 'numeracy2', 'response' => 'option1']
                ],
                'results' => ['rawScore' => 6]
            ],
            (object)[
                'id' => 'response2',
                'assessmentId' => 'assessment1',
                'assigned' => '14/12/2020 10:31:00',
                'started' => '16/12/2020 10:00:00',
                'completed' => '16/12/2020 10:46:00',
                'student' => (object)['id' => 'student1', 'yearLevel' => 4],
                'responses' => [
                    (object)['questionId' => 'numeracy1', 'response' => 'option2'],
                    (object)['questionId' => 'numeracy2', 'response' => 'option1']
                ],
                'results' => ['rawScore' => 10]
            ],
            (object)[
                'id' => 'response3',
                'assessmentId' => 'assessment1',
                'assigned' => '14/12/2021 10:31:00',
                'started' => '16/12/2021 10:00:00',
                'completed' => '16/12/2021 10:46:00',
                'student' => (object)['id' => 'student1', 'yearLevel' => 5],
                'responses' => [
                    (object)['questionId' => 'numeracy1', 'response' => 'option2'],
                    (object)['questionId' => 'numeracy2', 'response' => 'option1']
                ],
                'results' => ['rawScore' => 15]
            ]
        ];


        // Mock DataLoader instance
        $this->mockLoader = $this->createMock(DataLoader::class);
        $this->mockLoader->method('loadStudents')->willReturn($this->students);
        $this->mockLoader->method('loadAssessments')->willReturn($this->assessments);
        $this->mockLoader->method('loadQuestions')->willReturn($this->questions);

        // Mock methods used by ProgressReport
        $this->mockLoader->method('getMostRecentCompleted')
            ->willReturn($this->responses[2]); // last completed assessment
        $this->mockLoader->method('getResponseByStudentAssessment')
            ->willReturn($this->responses);


        // Inject mock loader into singleton via Reflection
        $reflection = new \ReflectionClass(DataLoader::class);
        $prop = $reflection->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, $this->mockLoader);


        // Instantiate the report generator
        $this->report = new ProgressReport();
    }


    public function testGenerateReturnsProgressDataForStudent()
    {
        $result = $this->report->generate('student1');
        $this->assertIsString($result);
        $this->assertStringStartsWith('Tony Stark', $result);
    }

    /**
     * @throws \Exception
     */
//    public function testGenerateReturnsErrorWhenNoCompletedAssessments(): void
//    {
//        $studentId = null; // Not exists id
//
//        // Mock loader behavior
//        $this->mockLoader->method('loadStudents')->willReturn([]);
//        $this->mockLoader->method('loadAssessments')->willReturn([]);
//        $this->mockLoader->method('loadQuestions')->willReturn([]);
//        $this->mockLoader->method('getResponseByStudentAssessment')
//            ->willReturn([]);
//        $this->mockLoader->method('getMostRecentCompleted')
//            ->willReturn(null);
//
//        $result = $this->report->generate($studentId);
//
//        $this->assertStringContainsString('Error: No completed assessments found', $result);
//    }


    /**
     * @throws \Exception
     */
    public function testGenerateReportShowsMultipleAttempts(): void
    {
        $output = $this->report->generate($this->students['student1']->id);

        $this->assertStringContainsString('Tony Stark', $output);
        $this->assertStringContainsString('3 times in total', $output);
    }
}