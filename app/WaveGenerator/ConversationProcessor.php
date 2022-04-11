<?php

namespace App\WaveGenerator;

use Exception;

/**
 * Start the processing of a conversation
 */
class ConversationProcessor {
    /**
     * Paths to the resources being processed
     *
     * @var array
     */
    protected $resourcePaths = [];

    /**
     * Class initializer
     *
     * @param string $userResourcePath
     * @param string $customerResourcePath
     */
    public function __construct(string $userResourcePath, string $customerResourcePath) {
        $this->resourcePaths['user'] = $userResourcePath;
        $this->resourcePaths['customer'] = $customerResourcePath;

        // echo 'CP initialized' . PHP_EOL;
    }

    /**
     * Processing conversation resources
     *
     * @return string|false
     */
    public function processConversation() {
        $conversationData = [];

        foreach ($this->resourcePaths as $streamType => $streamPath) {
            try {
                $streamReader = new FileReader($streamPath);
                $streamAnalyzer = new FileAnalyzer($streamReader);
                $streamProcessor = new StreamProcessor($streamAnalyzer);

                $conversationData[$streamType] = $streamProcessor->processStream();
            } catch (Exception $ex) {
                echo $ex->getMessage() . PHP_EOL;
                exit();
            }
        }

        return $this->getConversationResult($conversationData);
    }

    /**
     * Prepare a json formatted result of processed conversation data
     *
     * @param array $conversationData
     * @return string|false
     */
    protected function getconversationResult(array $conversationData) {
        $results = [
            "longest_user_monologue" =>  $conversationData['user']['longestMonologue'],
            "longest_customer_monologue" => $conversationData['customer']['longestMonologue'],
            "user_talk_percentage" => $conversationData['user']['talkPercentage'],
            "user" => $conversationData['user']['speechPeriods'],
            "customer" => $conversationData['customer']['speechPeriods']
        ];

        return json_encode($results);
    }
}