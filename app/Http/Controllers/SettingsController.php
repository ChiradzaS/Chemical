<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    //  public function function1(Request $request) // Type-hint Request
    // {
    //     // Get a specific query parameter
    //     $serverName = $request->query('serverName'); // Returns null if not present

    //     // You can also use $request->input() which checks query, then body (POST/PUT)
    //     // $serverName = $request->input('serverName');

    //     if ($serverName) {
    //         return "Function 1 called for server: " . $serverName;
    //     } else {
    //         return "Function 1 called (no server name provided)";
    //     }
    // }


function function1() {
    $projectPath = 'C:\\xampp\\htdocs\\LaravelCRUD';
    $command = "cd /d \"$projectPath\" && npm run dev";
    
    exec($command, $output, $returnCode);



    
        return response()->json([
            'status' => 'success',
            'message' => 'NPM dev started successfully.',
        ]);

}


    public function function2()
    {
        return 'Function 2 called';
    }

    public function function3()
    {
        return 'Function 3 called';
    }

    public function function4()
    {
        return 'Function 4 called';
    }

    public function function5()
    {
        return 'Function 5 called';
    }

    public function function6()
    {
        return 'Function 6 called';
    }

    public function function7()
    {
        return 'Function 7 called';
    }

    public function statuses(Request $request)
{

        $clientIp = gethostname();
        $serverIpV4 = gethostbyname($clientIp);
        $url = env('APP_URL1');
        $ur2 = env('APP_URL');


    


    $servers = [ 
        
        
        ['name' => 'Host Name', 'ip' => $clientIp, 'status' => 'maintenance'],
        ['name' => 'IPv4 address', 'ip' => $serverIpV4 , 'status' => 'maintenance'],
        ['name' => 'Local url', 'ip' => $url, 'status' => 'maintenance'],
        ['name' => 'Cloud url', 'ip' => $ur2, 'status' => 'maintenance'],
        ['name' => 'Apache Server', 'ip' => 'localhost:80', 'status' => $this->checkApache()],
        ['name' => 'MySQL Database', 'ip' => 'localhost:3306', 'status' => $this->checkMysql()],
        ['name' => 'Vite React', 'ip' => 'localhost:5173', 'status' => $this->checkVite()],
        ['name' => 'Mosquitto', 'ip' => 'localhost:1883', 'status' => $this->checkMosquitto()],
        // ['name' => 'Mqtt sub', 'ip' => $serverIpV4.':1883', 'status' => $this->testMqttPubSub($serverIpV4)],



    ];

    return response()->json($servers);
}



private function checkPort($host, $port, $timeout = 3) {
    $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}

private function checkUrl($url) {
    if (!$url) return 'maintenance';
    
    try {
        $context = stream_context_create([
            'http' => ['timeout' => 5, 'method' => 'HEAD']
        ]);
        $headers = @get_headers($url, 1, $context);
        return $headers !== false ? 'running' : 'stopped';
    } catch (Exception $e) {
        return 'stopped';
    }
}

private function checkApache() {
    return ($this->checkPort('localhost', 80) || $this->checkPort('localhost', 8080)) ? 'running' : 'stopped';
}

private function checkMysql() {
    
    return $this->checkPort('localhost', 3306) ? 'running' : 'stopped';
}

private function checkVite() {

    return ($this->checkPort('localhost', 5173) || 
            $this->checkPort('localhost', 3000) 
           ) ? 'running' : 'stopped';

}

private function checkMosquitto() {

    return ($this->checkPort('localhost', 1883)  
           ) ? 'running' : 'stopped';
}


// // Keep the pub/sub test as backup method
// private function testMqttPubSub($serverIpV4) {
//     try {
        
//         // Publish test message to fixed topic
//         $publishCommand = 'mosquitto_pub -h '.$serverIpV4 .' -p 1883 -t "test/topic" -m "testing" 2>&1';
        
//         // Subscribe to the same fixed topic (wait for 1 message)
//         $subscribeCommand = 'timeout 3 mosquitto_sub -h '.$serverIpV4 .'-p 1883 -t "test/topic" -C  2>&1';
        
//         // Publish message first
//         exec($publishCommand, $pubOutput, $pubReturnVar);
        
//         // Then try to receive it
//         exec($subscribeCommand, $subOutput, $subReturnVar);

//         Log::info(isset($subOutput[0]));
     
        
//         // Check if we received the expected message
//         $receivedMessage = isset($subOutput[0]) ? trim($subOutput[0]) : '';
//         return ($receivedMessage === 1) ? 'running' : 'stopped';
        
//     } catch (Exception $e) {
//         return 'stopped';
//     }
// }




}
