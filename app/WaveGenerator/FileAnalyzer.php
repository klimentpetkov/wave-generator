<?php

namespace App\WaveGenerator;

use App\Contracts\StreamAnalyzerInterface;
use App\Contracts\StreamReaderInterface;

/**
 * Analyzes a file with silence filtered data
 */
class FileAnalyzer implements StreamAnalyzerInterface {

    /**
     * All periods with speech
     *
     * @var array
     */
    protected $speechPeriods = [];

    /**
     * All periods with silence
     *
     * @var array
     */
    protected $silencePeriods = [];

    /**
     * Speech start marker in seconds
     *
     * @var float
     */
    protected $speechStartTime = 0.0;

    /**
     * Speech end marker in seconds
     *
     * @var float
     */
    protected $speechEndTime = 0.0;

    /**
     * Silence start marker in seconds
     *
     * @var float
     */
    protected $silenceStartTime = 0.0;

    /**
     * Silence end marker in seconds
     *
     * @var float
     */
    protected $silenceEndTime = 0.0;

    /**
     * Longest speech monologue in seconds
     *
     * @var float
     */
    protected $longestMonologue = 0.0;

    /**
     * Conversation total time in seconds
     *
     * @var float
     */
    protected $conversationTime = 0.0;

    /**
     * Total speech time in seconds
     *
     * @var float
     */
    protected $totalSpeechTime = 0.0;

    /**
     * Total silence time in seconds
     *
     * @var float
     */
    protected $totalSilenceTime = 0.0;

    /**
     * Carries what kind of line is being processed
     *
     * @var string  $currentLineSilenceType (start|end)
     */
    protected $currentLineSilenceType = '';

    /**
     * Stream reader
     *
     * @var StreamReaderInterface $streamReader
     */
    protected $streamReader;

    /**
     * Class initializer
     *
     * @param StreamReaderInterface $streamReader
     */
    public function __construct(StreamReaderInterface $streamReader) {
        $this->streamReader = $streamReader;
    }

    /**
     * Process provided stream resource by
     * iterating all available data
     *
     * @return void
     */
    public function processResource() : void {
        // echo 'Start processing resource' . PHP_EOL;

        do {
            $line = $this->streamReader->readData();

            if ($line)
                $this->processLine($line);
        } while($line);
    }

    /**
     * Provides gathered results
     *
     * @return void
     */
    public function getResults() {
        return [
            "longestMonologue" => round($this->longestMonologue, 3),
            "talkPercentage" => $this->calculateTalkPercentage(),
            "speechPeriods" => $this->speechPeriods
        ];
    }

    /**
     * Verifies a line to meet process requirements for string formatting
     *
     * @param string $line
     * @return boolean
     */
    protected function isLineVerified(string $line) : bool {
        $pattern = "/\[silencedetect\s@\s0x.*\]\ssilence_(start|end):\s(\d)+(.(\d)+)?(\s\|\ssilence_duration:\s(\d)+(.(\d)+)?)?/";
        $matchResult = preg_match_all($pattern, $line, $matches);

        if ($matchResult && count($matches[0]) && strlen(rtrim($line)) == strlen($matches[0][0])) {
            $this->currentLineSilenceType = $matches[1][0];
            return true;
        }

        echo "Line: {$line} was not verified!" . PHP_EOL;
        return false;
    }

    /**
     * Process a single line
     *
     * @param string $line
     * @return void
     */
    protected function processLine(string $line) : void  {
        // Removes new line from the end of the row
        $line = rtrim($line);
        // echo 'Start processing line: ' . $line . PHP_EOL;

        if ($this->isLineVerified($line)) {
            $method = 'process' . ucfirst($this->currentLineSilenceType) . 'SilenceTime';
            call_user_func_array([$this, $method], [$line]);
        }
    }

    /**
     * Process start silence time
     *
     * @param string $line
     * @return void
     */
    private function processStartSilenceTime(string $line) : void {
        // echo 'Process Start silence time' . PHP_EOL;
        $startText = substr($line, strpos($line, ']') + 2);
        $startSilenceTime = (float)explode(': ', $startText)[1];

        $this->silenceStartTime = $startSilenceTime;
        $this->speechEndTime = $startSilenceTime;

        $this->speechPeriods[] = [$this->speechStartTime, $this->speechEndTime];

        $monologTime = $this->speechEndTime - $this->speechStartTime;
        $this->totalSpeechTime += $monologTime;

        if ($this->longestMonologue < $monologTime)
            $this->longestMonologue = $monologTime;
    }

    /**
     * Process end silence time
     *
     * @param string $line
     * @return void
     */
    private function processEndSilenceTime(string $line) : void {
        // echo 'Process End silence time' . PHP_EOL;
        $endText = substr($line, strpos($line, ']') + 2);
        $parts = explode(' | ', $endText);
        $endSilenceTime = (float)explode(': ', $parts[0])[1];
        $silenceDuration = (float)explode(': ', $parts[1])[1];


        $this->silenceEndTime = $endSilenceTime;
        $this->speechStartTime = $endSilenceTime;

        // $this->silencePeriods[] = [$this->startSilenceTime, $this->endSilenceTime];
        // $this->totalSilenceTime += $silenceDuration;

        $this->conversationTime = $endSilenceTime;
    }

    /**
     * Calculate speech percentage over conversation time
     *
     * @return float
     */
    protected function calculateTalkPercentage() {
        return round($this->totalSpeechTime / $this->conversationTime * 100, 2);
    }
}