<?php

namespace App\Infrastructure;

use Exception;
use JsonMachine\Items;

/**
 * JSON STREAM READER
 * Handles streaming JSON parsing
 */
final readonly class JsonStreamReader
{


    /**
     * @param string $filePath
     * @throws Exception
     */
    public function __construct(private string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
    }

    /**
     * Parse JSON file and yield items one by one
     * uses JsonMachine if available, falls back to manual parsing
     * @throws Exception
     */
    public function parse(): \Generator
    {
        if (class_exists('\JsonMachine\Items')) {
            return $this->parseWithJsonMachine();
        } else {
            return $this->parseNative();
        }
    }

    /**
     * Parse using JsonMachine library (preferred)
     */
    private function parseWithJsonMachine(): \Generator
    {
        foreach (Items::fromFile($this->filePath) as $item) {
            yield $item;
        }
    }


    /**
     * Simple parse - decode entire file (fast for small files)
     * @throws Exception
     */
    private function parseNative(): \Generator
    {
        $data = json_decode(file_get_contents($this->filePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decode error: " . json_last_error_msg());
        }

        foreach ($data as $item) {
            yield $item;
        }
    }


}
