<?php

namespace Tests;

use App\DataLoader;
use PHPUnit\Framework\TestCase;

class DataLoaderTest extends TestCase
{
    private string $testDataPath;

    protected function setUp(): void
    {
        $this->testDataPath = __DIR__ . '/../data';
    }

    public function testLoadStudentsReturnsArray(): void
    {
        $loader = new DataLoader($this->testDataPath);
        $students = $loader->loadStudents();

        $this->assertIsArray($students);
        $this->assertNotEmpty($students);
    }

}