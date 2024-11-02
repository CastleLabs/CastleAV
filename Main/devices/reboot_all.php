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

    curl_setopt($ch, CURLOPT_TIMEOUT, 1);  // Set a very short timeout

    curl_exec($ch);
    curl_close($ch);
}

function rebootAllDevices() {
    $devices = [
        '192.168.8.12', '192.168.8.20', '192.168.8.17', '192.168.8.21', '192.168.8.25',
        '192.168.8.60', '192.168.8.61', '192.168.8.62', '192.168.8.63', '192.168.8.15',
        '192.168.8.13', '192.168.8.50', '192.168.8.51', '192.168.8.52', '192.168.8.53',
        '192.168.8.54', '192.168.8.55', '192.168.8.30', '192.168.8.10', '192.168.8.18',
        '192.168.8.19', '192.168.8.11', '192.168.8.27', '192.168.8.16', '192.168.8.26',
\       '192.168.8.70'
    ];
    
    foreach ($devices as $ip) {
        makeApiCall('POST', $ip, 'command/cli', 'reboot', 'text/plain');
    }

    return [
        'success' => true,
        'message' => "Reboot commands sent. Devices will reboot in approximately 90 seconds."
    ];
}

try {
    $result = rebootAllDevices();
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Unexpected error in rebootAllDevices: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred while sending reboot commands.'
    ]);
}
