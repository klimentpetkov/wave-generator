<?php

namespace App\WaveGenerator;

use App\Contracts\StreamAnalyzerInterface;

/**
 * Process a single silence periods stream
 */
class StreamProcessor {
    /**
     * Stream analyzer
     *
     * @var AnalyzerInterface
     */
    protected $streamAnalyzer;

    /**
     * Class initializer
     *
     * @param StreamAnalyzerInterface $streamAnalyzer
     */
    public function __construct(StreamAnalyzerInterface $streamAnalyzer)
    {
        $this->streamAnalyzer = $streamAnalyzer;
    }

    /**
     * Start processing resource
     *
     * @return void
     */
    public function processStream() {
        $this->streamAnalyzer->processResource();

        return $this->streamAnalyzer->getResults();
    }
}