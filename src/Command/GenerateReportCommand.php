<?php

namespace App\Command;

use App\Factories\ReportGeneratorFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateReportCommand extends Command
{
    protected static $defaultName = 'app:generate-report';

    protected function configure(): void
    {
        $this
            ->setName('app:generate-report')
            ->setDescription('Generate Diagnostic, Progress, or Feedback report for a student');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {

            $io = new SymfonyStyle($input, $output);

            $io->title('Generate Diagnostic Report');
            $io->block('Please enter the following');

            // --- Step 1: Ask for Student ID ---
            $studentId = $io->askQuestion(new Question("Student ID: "));

            // --- Step 2: Ask for Report Type  ---
            $reportType = $io->choice(
                'Select Report type to generate',
                ['1' => 'Diagnostic', '2' => 'Progress', '3' => 'Feedback'],
                '1'
            );


            $report = ReportGeneratorFactory::createReport(strtolower($reportType));

            //Output the Report
            $output->writeln($report->generate($studentId));

            $output->writeln('<comment>Report generated successfully!</comment>');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $output->writeln("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
