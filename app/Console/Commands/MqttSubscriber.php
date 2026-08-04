<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Bluerhinos\phpMQTT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Library\SerialNo;
use App\Models\Production;
use App\Models\Type;
use App\Models\Chemicaljobcarditem;
use App\Models\Stock;
use App\Models\StocksTrans;
use App\Models\ChemicalJobcard;
use App\Models\ChemicalProduct;
use App\Models\Workspace;
use App\Models\Customer;
use App\Models\Machinery;
use App\Models\Productionitem;
use App\Models\DocumentAudit;
use Illuminate\Support\Facades\View;
use App\Barcode\Barcode;
use Carbon\Carbon;
use DateTime;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe to MQTT topics and process incoming messages';
    protected $mqtt;
    protected $broker;
    protected $port;
    protected $clientId;
    protected $username;
    protected $password;

    public function __construct()
    {
        parent::__construct();

        $this->broker   = $this->getWindowsEthernetIP();
        $this->port     = env('MQTT_BROKER_PORT', 1883);
        $this->username = env('MQTT_BROKER_USERNAME', null);
        $this->password = env('MQTT_BROKER_PASSWORD', null);
        $this->clientId = 'ChemicalMQTTSubscriber_' . rand(1000, 9999);
    }

    private function getWindowsEthernetIP() {
        $output = [];
        exec('ipconfig /all', $output);

        $collecting = false;
        $isEthernet = false;

        foreach ($output as $line) {
            if (strpos($line, 'Ethernet adapter') !== false) {
                if (strpos($line, 'Wireless') === false && strpos($line, 'Wi-Fi') === false) {
                    $collecting = true;
                    $isEthernet = true;
                    continue;
                } else {
                    $collecting = false;
                    $isEthernet = false;
                }
            }

            if (strpos($line, 'Wireless LAN adapter') !== false ||
                strpos($line, 'Wi-Fi') !== false) {
                $collecting = false;
                $isEthernet = false;
                continue;
            }

            if ($collecting && $isEthernet && strpos($line, 'IPv4 Address') !== false) {
                preg_match('/\d+\.\d+\.\d+\.\d+/', $line, $matches);
                if (isset($matches[0])) {
                    return $matches[0];
                }
            }

            if ($collecting && trim($line) == '') {
                $collecting = false;
                $isEthernet = false;
            }
        }

        exec('wmic nic where "NetEnabled=true AND PhysicalAdapter=true AND NOT Description like \'%wireless%\' AND NOT Description like \'%wi-fi%\'" get IPAddress', $wmic_output);

        foreach ($wmic_output as $line) {
            if (preg_match('/\d+\.\d+\.\d+\.\d+/', $line, $matches)) {
                return $matches[0];
            }
        }

        return gethostbyname(gethostname());
    }

    public function handle()
    {
        $checkRunning = shell_exec('tasklist /FI "IMAGENAME eq php.exe" /FO CSV /V | findstr /i "mqtt:subscribe"');
        $processCount = substr_count($checkRunning, 'php.exe');

        if ($processCount > 1) {
            $this->info("Another instance of MQTT subscriber is already running. Exiting.");
            return 0;
        }

        $this->mqtt = new \Bluerhinos\phpMQTT($this->broker, $this->port, $this->clientId);

        if (!$this->mqtt->connect(true, NULL, $this->username, $this->password)) {
            $this->error("Failed to connect to MQTT Broker at {$this->broker}:{$this->port}");
            return 1;
        }

        $this->info("Connected to MQTT broker at {$this->broker}:{$this->port}");

        $topics = [
            'chemicaljobcard/request'           => ['qos' => 0, 'function' => [$this, 'handleJobcardRequest']],
            'chemicalrecycleproduction/request' => ['qos' => 0, 'function' => [$this, 'handleRecycleProductionRequest']],
            'chemicaltest/topic'                => ['qos' => 0, 'function' => [$this, 'handleTestTopicRequest']],
            'chemicalproductionitem/request'    => ['qos' => 0, 'function' => [$this, 'handleProductiobItemRequest']],
            'chemicaluser/request'       => ['qos' => 0, 'function' => [$this, 'handleQryUserRequest']],
            'chemicalqryallocations/request'    => ['qos' => 0, 'function' => [$this, 'handleAllocationRequest']],
            'chemicaluserr/request'              => ['qos' => 0, 'function' => [$this, 'handleUsersLoginRequest']],
            'chemicalproduction/request'        => ['qos' => 0, 'function' => [$this, 'handleProductionStoreRequest']],
            'chemicalqryjobcard/request'        => ['qos' => 0, 'function' => [$this, 'handleJobcardShowRequest']],
            'chemicalqrytype/request'           => ['qos' => 0, 'function' => [$this, 'handleTypeIndexRequest']],
            'chemicalitem/request'              => ['qos' => 0, 'function' => [$this, 'handleProductionItemStoreRequest']],
        ];

        $this->mqtt->subscribe($topics, 0);
        $this->info("Subscribed to topics: " . implode(', ', array_keys($topics)));

        $this->info("Listening for messages... Press Ctrl+C to stop.");

        while ($this->mqtt->proc()) {}

        $this->mqtt->close();
    }

    public function handleProductiobItemRequest($topic, $message)
    {
        $this->info("Received jobcard request: $message");
        Log::info("Received message from MQTT - Topic: $topic, Message: $message");

        $jobcardData = json_decode($message, true);

        $data = $jobcardData["storeType=1"];
        $this->info(json_encode($data, JSON_PRETTY_PRINT));

        $storeType = $data['storeType'] ?? null;
        $productId = $data['productId'] ?? null;
        $weight_per_bale = 0;

        try {

            $existingCode = Productionitem::where('unique_code', $data['code'])->exists();

            if ($existingCode) {
                \Log::info('Production item already exists', [
                    'unique_code' => $data['code'],
                    'timestamp' => now()
                ]);

                return;
            }

            $processId = $data['processId'];
            $quantity  = $data['qnt'] ?? 0;

            if ($processId == 24) {
                $quantity = $data['weight'] ?? 1;
                $weight_per_bale = ChemicalProduct::where('id', $productId)->value('maxWeightPerProduct') ?? 0;
            }

            $currentHour = now()->hour;
            $currentDate = now();

            $currentDateTime = new DateTime();
            $currentTime = $currentDateTime->format('H:i');

            if ($currentTime >= '00:00' && $currentTime <= '06:00') {
                $currentDate->modify('-1 day');
            }

            $dataToInsert = [
                'productionId'    => $data['productionId'],
                'jobcarditemId'   => $data['jobcarditemId'],
                'other'           => 'none',
                'productId'       => $data['productId'],
                'userId'          => $data['userId'],
                'weight'          => 0,
                'processId'       => $data['processId'],
                'qnt'             => $quantity,
                'unitId'          => $data['unitId'],
                'machineId'       => $data['machine'],
                'tms'             => now()->format('H:i:s'),
                'employeeId'      => $data['userId'],
                'shiftId'         => $data['shiftId'],
                'wpProduct'       => $data['wpProduct'],
                'weightState'     => $data['weightState'],
                'tempId'          => $data['tempId'] ?? 0,
                'serialNo'        => SerialNo::generateSerialNumber(),
                'unique_code'     => $data['code'],
                'rollId'          => $data['rollId'],
                'weight_per_bale' => $weight_per_bale,
                'dateCreated'     => $currentDate,
            ];

            Productionitem::insert($dataToInsert);

            $this->info("Published response to chemicalproductionitem/response");

        } catch (\Exception $e) {

            Log::error("Error processing jobcardData1 request: " . $e->getMessage());
            $this->error("Error processing jobcardData1 request: " . $e->getMessage());

            $this->mqtt->publish("chemicalproductionitem/response", json_encode([
                'error' => 'Failed to process request'
            ]), 0);
        }
    }

    public function handleQryUserRequest($topic, $message)
    {
        $this->info("Received user details request: $message");
        Log::info("Received message from MQTT - Topic: $topic, Message: $message");

        $data = json_decode($message, true);

        $tmpId = $data['id'] ?? null;

        try {

            $user = User::find($tmpId);

            if (!$user) {
                $this->mqtt->publish("chemicaluser/response", json_encode([
                    'error' => 'User not found in database.'
                ]), 0);
                return;
            }

            $this->mqtt->publish("chemicaluser/response", json_encode($user), 0);
            $this->info("Published response to chemicaluser/response");

        } catch (\Exception $e) {

            Log::error("Error processing user details request: " . $e->getMessage());
            $this->error("Error processing user details request: " . $e->getMessage());

            $this->mqtt->publish("chemicaluser/response", json_encode([
                'error' => 'Failed to process request'
            ]), 0);
        }
    }

    public function handleJobcardRequest($topic, $message)
    {
        $this->info("Received jobcard request: $message");
        Log::info("Received message from MQTT - Topic: $topic, Message: $message");

        $data = json_decode($message, true);
        $requestId = $data['requestId'];
        $jobId = $data['jobId'];

        try {
            $jobcard = DB::table('chemical_jobcard')->where('id', $jobId)->first();

            $response = [
                'requestId' => $requestId,
                'jobcard' => $jobcard
            ];

            $this->mqtt->publish("chemicaljobcard/response/$requestId", json_encode($response), 0);
            $this->info("Published response to chemicaljobcard/response/$requestId");
        } catch (\Exception $e) {
            Log::error("Error processing jobcard request: " . $e->getMessage());
            $this->error("Error processing jobcard request: " . $e->getMessage());

            $this->mqtt->publish("chemicaljobcard/response/$requestId", json_encode([
                'requestId' => $requestId,
                'error' => 'Failed to process request'
            ]), 0);
        }
    }

    public function handleTestTopicRequest($topic, $message)
    {
        $this->info("Received test message: $message");
        Log::info("Received message from MQTT - Topic: $topic, Message: $message");

        $response = is_string($message) ? $message : json_encode($message);

        $this->mqtt->publish("chemicaltest/topic", $response, 0);
        Log::info("Pushed response back to topic '$topic': " . $response);
    }

    public function handleUsersLoginRequest($topic, $message)
    {
        $data = json_decode($message, true);
        $requestId = 10;
        $tmpId = $data['id'] ?? null;

        try {

            $user = User::find($tmpId);
            if (!$user) {
                $response = ['requestId' => $requestId, 'error' => 'User not found in database.'];
                $this->mqtt->publish("chemicaluser/response", json_encode($response), 0);
                $this->info("User not found, published error to chemicaluser/response/$requestId");
                return;
            }

            $this->mqtt->publish("chemicaluser/response", json_encode($user), 0);
            $this->info("Published user response to chemicaluser/response");

        } catch (\Exception $e) {
            Log::error("Error processing user login request: " . $e->getMessage());
            $this->error("Error processing user login request: " . $e->getMessage());

            $this->mqtt->publish("chemicaluser/response", json_encode([
                'requestId' => $requestId,
                'error' => 'Failed to process request'
            ]), 0);
        }
    }

    public function handleRecycleProductionRequest($topic, $message)
    {
        $this->info("Received recycle production request: $message");
        Log::info("Received message from MQTT - Topic: $topic, Message: $message");

        $data = json_decode($message, true);
        $requestId = $data['requestId'];

        try {
            $recycleId = DB::table('recycle_production')->insertGetId([
                'operator' => $data['operator'],
                'kilos' => $data['kilos'],
                'machineId' => $data['machineId'],
                'materialTypeId' => $data['materialTypeId'],
                'shiftId' => $data['shiftId'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $response = [
                'requestId' => $requestId,
                'recycleId' => $recycleId
            ];

            $this->mqtt->publish("chemicalrecycleproduction/response/$requestId", json_encode($response), 0);
            $this->info("Published response to chemicalrecycleproduction/response/$requestId");

        } catch (\Exception $e) {
            Log::error("Error processing recycle production request: " . $e->getMessage());
            $this->error("Error processing recycle production request: " . $e->getMessage());

            $this->mqtt->publish("chemicalrecycleproduction/response/$requestId", json_encode([
                'requestId' => $requestId,
                'error' => 'Failed to process request'
            ]), 0);
        }
    }

    public function handleAllocationRequest($topic, $message)
    {
        $this->info(">>> handleLoginRequest triggered");

        $data = json_decode($message, true);
        $this->info("Raw message: " . $message);

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $this->info("Username: " . ($username ?? 'NULL') . " | Password: " . ($password ?? 'NULL'));

        try {
            if (!$username || !$password) {
                $this->info("Missing credentials detected");
                $this->mqtt->publish("chemicalqryallocations/response", json_encode(['error' => 'Missing credentials.']), 0);
                $this->info("Missing credentials, published error to chemicalqryallocations/response");
                return;
            }

            $this->info("Looking up user in DB...");
            $user = DB::table('users')->where('name', $username)->first();
            $this->info("DB result: " . ($user ? json_encode($user) : 'NULL'));

            if (!$user || $user->other !== $password) {
                $this->info("Auth failed - user not found or password mismatch");
                $this->mqtt->publish("chemicalqryallocations/response", json_encode(['user_id' => 0]), 0);
                $this->info("Authentication failed, published response to chemicalqryallocations/response");
                return;
            }

            $this->info("Auth success - publishing user_id: " . $user->id);
            $this->mqtt->publish("chemicalqryallocations/response", json_encode([
                'user_id' => (int) $user->id,
            ]), 0);
            $this->info("Published login response to chemicalqryallocations/response");

        } catch (\Exception $e) {
            $this->info("EXCEPTION CAUGHT: " . $e->getMessage());
            Log::error("Error processing login request", [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'data'    => $data ?? null,
            ]);

            $this->mqtt->publish("chemicalqryallocations/response", json_encode([
                'error' => 'Failed to process request'
            ]), 0);
        }
    }

    public function handleProductionStoreRequest(string $topic, string $rawMessage): void
    {
        Log::info('MQTT PRODUCTION/REQUEST: entered', ['raw' => $rawMessage]);

        $message = json_decode($rawMessage, true);

        if (!$message) {
            Log::error('MQTT PRODUCTION/REQUEST: invalid JSON', ['raw' => $rawMessage]);
            return;
        }

        try {

            $thresholdTime = Carbon::now()->subHours(8);

            Production::where('created_at', '<', $thresholdTime)
                ->update(['stateId' => 45]);

            Log::info('MQTT PRODUCTION/REQUEST: stale production update done');

            $machineryId = $message['machineryId'] ?? null;
            $machinery   = $machineryId ? Machinery::find($machineryId) : null;

            $processId = $machinery
                ? $machinery->processId
                : ($message['processId'] ?? null);

            Log::info('MQTT PRODUCTION/REQUEST: processId resolved', ['processId' => $processId]);

            $production              = new Production;
            $production->refNo       = 0;
            $production->other       = 0;
            $production->value       = 0;
            $production->processId   = $processId;
            $production->machineryId = $machineryId;
            $production->userId      = $message['userId'] ?? null;
            $production->employeeId  = $message['userId'] ?? null;
            $production->stateId     = 62;

            $current_time        = Carbon::now();
            $morning_shift_start = Carbon::createFromTime(6, 0, 0);
            $evening_shift_start = Carbon::createFromTime(18, 0, 0);

            $production->shiftId = $current_time->between($morning_shift_start, $evening_shift_start)
                ? 31
                : 30;

            $production->prodDate  = now();
            $production->startTime = date('H:i:s');

            $jobcard = $message['jobcard'] ?? '';
            $parts   = explode('-', $jobcard);
            $production->currentJobcard = $parts[0];

            $production->save();

            Log::info('MQTT PRODUCTION/REQUEST: save successful', ['productionId' => $production->id]);

            $this->mqtt->publish('chemicalproduction/response', json_encode($production->toArray()), 0, false);

        } catch (\Throwable $e) {
            Log::error('MQTT PRODUCTION/REQUEST: exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $this->mqtt->publish('chemicalproduction/response', json_encode([
                'error'   => 'Failed to create production record',
                'message' => $e->getMessage(),
            ]), 0, false);
        }
    }

    public function handleJobcardShowRequest($topic, $message)
    {
        $data      = json_decode($message, true);
        $requestId = $data['requestId'] ?? null;
        $id        = $data['id'] ?? null;

        try {
            $dashPos    = strpos($id, '-');
            $itemId     = $dashPos !== false ? substr($id, 0, $dashPos) : $id;
            $wpProduct  = $dashPos !== false ? (int) substr($id, $dashPos + 1) : 0;

            $item = Chemicaljobcarditem::where('id', $itemId)
                ->select('jobCardId', 'productId')
                ->first();

            $jobcardId = $item->jobCardId;
            $productId = $item->productId;

            if ($dashPos !== false) {
                $jobcarditems = Chemicaljobcarditem::where('jobCardId', $jobcardId)
                    ->where('processId', 24)
                    ->get();

                $productId = $jobcarditems->first()?->productId ?? $productId;
            } else {
                $jobcarditems = Chemicaljobcarditem::where('id', $itemId)->get();
            }

            $product = ChemicalProduct::where('id', $productId)->get();
            $jobcard = ChemicalJobcard::where('id', $jobcardId)->get();

            $response = [
                'requestId'    => $requestId,
                'product'      => $product,
                'jobcarditems' => $jobcarditems,
                'jobcard'      => $jobcard,
                'wpProduct'    => $wpProduct,
            ];

            $this->mqtt->publish("chemicalqryjobcard/response", json_encode($response), 0);
            $this->info("Published jobcard show response to chemicalqryjobcard/show");

        } catch (\Exception $e) {
            Log::error("Error processing jobcard show request: " . $e->getMessage());

            $this->mqtt->publish("chemicalqryjobcard/reponse", json_encode([
                'requestId' => $requestId,
                'error'     => 'Failed to process request'
            ]), 0);
        }
    }

    public function handleTypeIndexRequest($topic, $message)
    {
        $data = json_decode($message, true);
        $requestId = $data['requestId'] ?? null;

        $type     = $data['type']     ?? null;
        $machine  = $data['machine']  ?? null;
        $name     = $data['name']     ?? null;
        $customer = $data['customer'] ?? null;
        $action   = $data['action']   ?? null;

        try {

            if ($customer !== null) {
                $result = Customer::where('id', $customer)->value('name');
                $this->mqtt->publish("chemicalqrytype/response", json_encode([
                    'requestId' => $requestId,
                    'data'      => $result
                ]), 0);
                return;
            }

            if ($name !== null) {
                $result = Type::where('id', $name)->value('name');
                $this->mqtt->publish("chemicalqrytype/response", json_encode([
                    'requestId' => $requestId,
                    'data'      => $result
                ]), 0);
                return;
            }

            if ($machine !== null) {
                if ($machine === 'machineBagTop') {
                    $result = Machinery::where('processId', 24)->where('description', 'top')->orderBy('id')->get();
                } elseif ($machine === 'machineBagBottom') {
                    $result = Machinery::where('processId', 24)->where('description', 'btm')->orderBy('id')->get();
                } elseif ($machine === 'machineExTop') {
                    $result = Machinery::where(function ($query) {
                            $query->where('name', 'like', 'ex%')
                                  ->orWhere('name', 'like', 'pe%')
                                  ->orWhere('name', 'like', 'dr%');
                        })->where('description', 'top')->orderBy('id')->get();
                } elseif ($machine === 'machineExBottom') {
                    $result = Machinery::where(function ($query) {
                            $query->where('name', 'like', 'ex%')
                                  ->orWhere('name', 'like', 'pe%');
                        })->where('description', 'btm')->orderBy('id')->get();
                } else {
                    $result = Machinery::orderBy('id')->get();
                }

                $this->mqtt->publish("chemicalqrytype/response", json_encode([
                    'requestId' => $requestId,
                    'data'      => $result
                ]), 0);
                return;
            }

            if ($type !== null) {
                $result = Type::where('groupType', $type)->get();
                $this->mqtt->publish("chemicalqrytype/response", json_encode([
                    'requestId' => $requestId,
                    'data'      => $result
                ]), 0);
                return;
            }

            if ($action !== null && trim($action) === 'query') {
                $searchTerm = $data['searchInput'] ?? null;
                $result = $searchTerm !== null
                    ? Type::where('name', 'like', '%' . $searchTerm . '%')->get()
                    : Type::where('name', '<>', '%%')->get();

                $this->mqtt->publish("chemicalqrytype/response", json_encode([
                    'requestId' => $requestId,
                    'data'      => $result
                ]), 0);
                return;
            }

            $result = Type::orderBy('groupType', 'asc')->take(1000)->get();
            $this->mqtt->publish("chemicalqrytype/response", json_encode([
                'requestId' => $requestId,
                'data'      => $result
            ]), 0);
            $this->info("Published type index response to chemicalqrytype/index");

        } catch (\Exception $e) {
            Log::error("Error processing type index request: " . $e->getMessage());

            $this->mqtt->publish("chemicalqrytype/response", json_encode([
                'requestId' => $requestId,
                'error'     => 'Failed to process request'
            ]), 0);
        }
    }

public function handleProductionItemStoreRequest($topic, $message)
{
    $data = json_decode($message, true);
    $requestId = $data['requestId'] ?? null;
    $productId = $data['productId'] ?? null;

    try {

        $weight_per_bale = ChemicalProduct::where('id', $productId)->value('maxWeightPerProduct') ?? 0;

        $productionitem                  = new Productionitem;
        $productionitem->productionId    = $data['productionId']  ?? null;
        $productionitem->jobcarditemId   = $data['jobcarditemId'] ?? null;
        $productionitem->other           = 'none';
        $productionitem->productId       = $productId;
        $productionitem->userId          = $data['userId']        ?? null;
        $productionitem->qnt             = $data['qnt']           ?? null;
        $productionitem->unitId          = $data['unitId']        ?? null;
        $productionitem->processId       = $data['processId']     ?? null;
        $productionitem->machineId       = $data['machineryId']   ?? null;
        $productionitem->tms             = date('H:i:s');
        $productionitem->employeeId      = $data['userId']        ?? null;
        $productionitem->shiftId         = $data['shiftId']       ?? null;
        $productionitem->weight          = $data['weight']        ?? null;
        $productionitem->wpProduct       = $data['wpProduct']     ?? null;
        $productionitem->weightState     = $data['weightState']   ?? null;
        $productionitem->serialNo        = SerialNo::generateSerialNumber();
        $productionitem->weight_per_bale = $weight_per_bale;
        $productionitem->save();

        // ── Stock addition + transaction log ──────────────────────────
        $stock = DB::table('stocks')
            ->where('productId', $productionitem->productId)
            ->first();

        if ($stock) {
            $newQnt = $stock->qnt + $productionitem->qnt;

            DB::table('stocks')
                ->where('productId', $productionitem->productId)
                ->update([
                    'prvqnt'     => $stock->qnt,
                    'qnt'        => $newQnt,
                    'updated_at' => now(),
                ]);

            DB::table('stocks_trans')->insert([
                'stockId'    => $stock->id,
                'docId'      => $productionitem->id,
                'docType'    => 105,
                'qnt'        => $productionitem->qnt,
                'userId'     => $productionitem->userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            Log::warning('No stock row found for product', [
                'productId'        => $productionitem->productId,
                'productionItemId' => $productionitem->id,
            ]);
        }

        // ── Jobcard qnt deduction ────────────────────────────────────
        try {

            if (!empty($productionitem->jobcarditemId)) {

                $jobcardItem = Chemicaljobcarditem::find($productionitem->jobcarditemId);

                if (!$jobcardItem) {
                    Log::warning('JobcardItem or related Jobcard not found', [
                        'jobcarditemId' => $productionitem->jobcarditemId
                    ]);
                } else {

                    $newQnt = $jobcardItem->quantity - $productionitem->qnt;
                    $jobcardItem->quantity = $newQnt;
                    $jobcardItem->save();

                    Log::info('Jobcard item qnt updated', [
                        'jobcarditemId' => $jobcardItem->id,
                        'qntProduced'   => $productionitem->qnt,
                        'newQnt'        => $jobcardItem->quantity,
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error processing jobcard: ' . $e->getMessage(), [
                'exception'     => $e,
                'jobcarditemId' => $productionitem->jobcarditemId ?? null
            ]);
        }

        $this->mqtt->publish("chemicalitem/response", json_encode([
            'requestId'        => $requestId,
            'productionItemId' => $productionitem->id,
            'jobcardQuantity'  => 10000,
            'barcode'          => 123456789,
            'balestickers'     => 1,
            'packetstickers'   => 1,
        ]), 0);

        $this->info("Published production item store response to chemicalitem/response");
        Log::info("adding items with mqtt");

    } catch (\Exception $e) {
        Log::error("Error processing production item store request: " . $e->getMessage());

        $this->mqtt->publish("chemicalitem/response", json_encode([
            'requestId' => $requestId,
            'error'     => 'Failed to process request'
        ]), 0);
    }
}

}