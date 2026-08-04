<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    private $server = '192.168.1.105';
    private $port = 1883;
    private $clientId;

    public function __construct()
    {
        $this->clientId = 'laravel_mqtt_client_' . uniqid();
    }

    public function subscribeToTopic($topic = 'test/topic')
    {
        try {
            Log::info("Attempting to connect to MQTT broker at {$this->server}:{$this->port}");

            $mqtt = new MqttClient($this->server, $this->port, $this->clientId);
            
            $connectionSettings = (new ConnectionSettings())
                ->setKeepAliveInterval(60);

            // Connect with clean session
            $mqtt->connect($connectionSettings, true);

            Log::info("Successfully connected to MQTT broker. Subscribing to topic: {$topic}");

            // Subscribe to the topic
            $mqtt->subscribe($topic, function (string $receivedTopic, string $message) {
                // Log and display raw message details
                Log::info("Raw MQTT Message Details:");
                Log::info("Topic: {$receivedTopic}");
                Log::info("Raw Message: {$message}");

                // Pretty print and display message for better readability
                $this->displayMessageDetails($message);

                // Parse the JSON message
                $data = json_decode($message, true);

                if ($data) {
                    try {
                        // Log parsed data
                        Log::info("Parsed Message Data:", $data);

                        // Validate required fields
                        if (!isset($data['productionId']) || !isset($data['jobcarditemId'])) {
                            Log::warning("Received incomplete MQTT message: " . json_encode($data));
                            return;
                        }

                        // Insert into database
                        $insertedId = DB::table('processed_items')->insertGetId([
                            'qnt' => $data['qnt'] ?? null,
                            'weight' => $data['weight'] ?? null,
                            'jobcarditemId' => $data['jobcarditemId'],
                            'productionId' => $data['productionId'],
                            'machine' => $data['machine'] ?? null,
                            'userId' => $data['userId'] ?? null,
                            'shiftId' => $data['shiftId'] ?? null,
                            'productId' => $data['productId'] ?? null,
                            'unitId' => $data['unitId'] ?? null,
                            'wpProduct' => $data['wpProduct'] ?? null,
                            'processId' => $data['processId'] ?? null,
                            'weightState' => $data['weightState'] ?? null,
                            'code' => $data['code'] ?? null,
                            'rollId' => $data['rollId'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Log::info("Processed and stored MQTT message. Inserted ID: {$insertedId}");
                    } catch (\Exception $e) {
                        Log::error("Error processing MQTT message: " . $e->getMessage());
                        Log::error("Message data: " . json_encode($data));
                    }
                } else {
                    Log::warning("Received invalid JSON message: {$message}");
                }
            });

            // Keep listening for messages
            $mqtt->loop(true);

        } catch (\Exception $e) {
            Log::error("MQTT Subscription Error: " . $e->getMessage());
        }
    }

    /**
     * Display message details in a readable format
     * 
     * @param string $message
     */
    private function displayMessageDetails(string $message)
    {
        // Attempt to parse JSON and pretty print
        $parsedMessage = json_decode($message, true);
        
        if ($parsedMessage) {
            // If valid JSON, use print_r for structured display
            Log::info("Formatted Message Details:");
            Log::info(print_r($parsedMessage, true));
        } else {
            // If not JSON, log as is
            Log::info("Unstructured Message Content: {$message}");
        }

        // Optional: You could add console output if running via artisan command
        if (app()->runningInConsole()) {
            echo "Received MQTT Message:\n";
            echo "------------------------\n";
            echo "Raw Message: {$message}\n";
            
            if ($parsedMessage) {
                echo "Parsed Message:\n";
                print_r($parsedMessage);
            }
            echo "------------------------\n";
        }
    }
}