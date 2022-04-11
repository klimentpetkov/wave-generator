<?php

namespace App\Contracts;

interface StreamAnalyzerInterface {
    public function __construct(StreamReaderInterface $sreamReader);
    public function processResource();
    public function getResults();
}