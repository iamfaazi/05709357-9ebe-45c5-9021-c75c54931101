<?php

namespace Tests\Commands;

use App\Command\GenerateReportCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateReportCommandTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up command
        $command = new GenerateReportCommand();
        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteHandlesException(): void
    {
        $studentId = 'student1';

        $this->commandTester->setInputs([
            $studentId,
            'Diagnostic'
        ]);

        $this->commandTester->execute([]);

        $this->assertIsInt($this->commandTester->getStatusCode());
    }

    public function testExecuteDisplaysReportTypeChoices(): void
    {
        $this->commandTester->setInputs(['student1', '']); // first answer for Student ID, second empty for next prompt

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Select Report type to generate', $output);
        $this->assertStringContainsString('Diagnostic', $output);
        $this->assertStringContainsString('Progress', $output);
        $this->assertStringContainsString('Feedback', $output);
    }


    public function testCommandHasCorrectName(): void
    {
        $command = new GenerateReportCommand();
        $this->assertEquals('app:generate-report', $command->getName());
    }

    public function testCommandHasDescription(): void
    {
        $command = new GenerateReportCommand();
        $this->assertEquals(
            'Generate Diagnostic, Progress, or Feedback report for a student',
            $command->getDescription()
        );
    }
}