<?php

namespace Tests\Reports;

use App\Command\GenerateReportCommand;
use App\Reports\DiagnosticReport;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DiagnosticReportTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for each report type
        $this->diagnosticReportMock = $this->createMock(DiagnosticReport::class);

        // Set up command
        $command = new GenerateReportCommand();
        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

//$this->commandTester->setInputs(['student1', '']); // first answer for Student ID, second empty for next prompt

    public function testExecuteDisplaysPromptForStudentId(): void
    {
        $this->commandTester->setInputs(['123', 'Diagnostic']);
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Student ID:', $output);
    }

    public function testShowGeneratesDiagnosticReportOptionToGenerate(): void
    {
        $this->commandTester->setInputs(['123', 'Diagnostic']);
        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Select Report type to generate', $output);
        $this->assertStringContainsString('Diagnostic', $output);
    }

}