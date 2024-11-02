<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

function makeApiCall($method, $ip, $endpoint, $data = null, $contentType = null) {
    $url = "http://$ip/cgi-bin/api/$endpoint";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($contentType !== null) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: $contentType",
            'Content-Length: ' . strlen($data)
        ]);
    }

    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    
    $error = null;
    if (curl_errno($ch)) {
        $error = curl_error($ch);
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'error' => $error,
        'httpCode' => $httpCode
    ];
}

function fixRockBot() {
    $devices = [
        '192.168.8.15' => '6',
        '192.168.8.16' => '7',
        '192.168.8.25' => '8'
    ];
    
    $responses = [];
    $allSuccess = true;

    foreach ($devices as $ip => $volume) {
        try {
            $result = makeApiCall('POST', $ip, 'command/audio/stereo/volume', $volume, 'text/plain');
            
            if ($result['error']) {
                throw new Exception($result['error']);
            }
            
            if ($result['httpCode'] !== 200) {
                throw new Exception("HTTP Error: " . $result['httpCode']);
            }
            
            $data = json_decode($result['response'], true);
            $success = isset($data['data']) && $data['data'] === 'OK';
            
            if (!$success) {
                throw new Exception("Unexpected response: " . $result['response']);
            }
            
            $responses[$ip] = [
                'success' => true,
                'message' => "Volume set to $volume successfully"
            ];
        } catch (Exception $e) {
            error_log("Error adjusting volume for IP $ip: " . $e->getMessage());
            $responses[$ip] = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
            $allSuccess = false;
        }
    }

    return [
        'success' => $allSuccess,
        'message' => $allSuccess ? 'RockBot fixed successfully. All volumes adjusted.' : 'Some devices failed to adjust volume',
        'details' => $responses
    ];
}

try {
    $result = fixRockBot();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Unexpected error in fixRockBot: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred while fixing RockBot',
        'details' => ['error' => $e->getMessage()]
    ]);
}
