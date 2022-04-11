<?php

namespace App\WaveGenerator;

use App\Contracts\StreamReaderInterface;
use Exception;

/**
 * Opens a file as a resource, reads data, and closes the file after no more data
 */
class FileReader implements StreamReaderInterface
{
    /**
     * A path to streamed resource
     *
     * @var string
     */
    protected $streamPath = '';

    /**
     * Opened stream to access data from
     *
     * @var [type]
     */
    protected $stream = null;

    /**
     * Class initializer
     *
     * @param [type] $streamPath
     */
    public function __construct($streamPath) {
        $this->streamPath = $streamPath;

        $this->openResource();
    }

    /**
     * Opens a stream resource for reading
     *
     * @return void
     */
    protected function openResource() {
        if (!file_exists($this->streamPath))
            throw new Exception("Stream @ {$this->streamPath} do not exist!");

        $this->stream = fopen($this->streamPath, 'r');

        if (!$this->stream)
            throw new Exception("Stream @ {$this->streamPath} is not accessible!");
    }

    /**
     * Reads a chunk of data
     *
     * @return string|bool
     */
    public function readData() {
        if (!$this->stream)
            return false;

        if (!feof($this->stream)) {
            $line = fgets($this->stream);

            if (!$line || empty($line)) {
                $this->closeResource();

                return false;
            }

            return $line;
        } else {
            $this->closeResource();

            return false;
        }
    }

    /**
     * Closes a resource
     *
     * @return void
     */
    public function closeResource() {
        fclose($this->stream);

        // echo "Resource closed!" . PHP_EOL;
    }
}