<?php

require __DIR__.'/vendor/autoload.php';

use App\Config\Config;
use App\WaveGenerator\ConversationProcessor;

$conversationProcessor = new ConversationProcessor(Config::USER_RESOURCE_PATH, Config::CUSTOMER_RESOURCE_PATH);
var_dump($conversationProcessor->processConversation());

echo PHP_EOL . '...END...' . PHP_EOL;