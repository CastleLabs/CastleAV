<?php
/**
 * Utility functions for AV Controls for Just Add Power receivers
 * 
 * This file contains all the necessary utility functions for interacting with
 * Just Add Power 3G devices, handling errors, logging, and generating HTML forms.
 *
 * @author Seth Morrow
 * @version 1.9
 * @date 2023-09-04
 */

/**
 * Function to handle API calls to the device
 * 
 * @param string $method - The HTTP method to be used for the API call (e.g., GET, POST)
 * @param string $deviceIp - The IP address of the device to interact with
 * @param string $endpoint - The specific API endpoint to call
 * @param mixed $data - Optional data to send with the API request (default is null)
 * @param string $contentType - The content type of the request (default is application/x-www-form-urlencoded)
 * @return string - The response from the API call
 * @throws Exception if the API call fails
 */
function makeApiCall($method, $deviceIp, $endpoint, $data = null, $contentType = 'application/x-www-form-urlencoded') {
    $apiUrl = 'http://' . $deviceIp . '/cgi-bin/api/' . $endpoint;
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data !== null) {
        if ($contentType === 'application/json' && !is_string($data)) {
            $data = json_encode($data);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: ' . $contentType));
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT);

    $result = curl_exec($ch);

    if ($result === false) {
        throw new Exception('cURL error: ' . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        throw new Exception('HTTP error: ' . $httpCode . ' - Response: ' . $result);
    }

    return $result;
}

/**
 * Function to get the current volume setting from a device
 * 
 * @param string $deviceIp - The IP address of the device
 * @return int|null - The current volume level, or null if not set or in case of an error
 */
function getCurrentVolume($deviceIp) {
    try {
        $response = makeApiCall('GET', $deviceIp, 'details/audio/stereo/volume');
        $data = json_decode($response, true);
        return isset($data['data']) ? intval($data['data']) : null;
    } catch (Exception $e) {
        logMessage('Error getting current volume: ' . $e->getMessage(), 'error');
        return null;
    }
}

/**
 * Function to get the current channel setting from a device
 * 
 * @param string $deviceIp - The IP address of the device
 * @return int|null - The current channel number, or null if not set or in case of an error
 */
function getCurrentChannel($deviceIp) {
    try {
        $response = makeApiCall('GET', $deviceIp, 'details/channel');
        $data = json_decode($response, true);
        return isset($data['data']) ? intval($data['data']) : null;
    } catch (Exception $e) {
        logMessage('Error getting current channel: ' . $e->getMessage(), 'error');
        return null;
    }
}

/**
 * Function to set the volume on a device
 * 
 * @param string $deviceIp - The IP address of the device
 * @param int|string $volume - The volume level to set
 * @return bool - True if successful, false otherwise
 */
function setVolume($deviceIp, $volume) {
    try {
        $response = makeApiCall('POST', $deviceIp, 'command/audio/stereo/volume', $volume, 'text/plain');
        $data = json_decode($response, true);
        return isset($data['data']) && $data['data'] === 'OK';
    } catch (Exception $e) {
        logMessage('Error setting volume: ' . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Function to set the channel on a device
 * 
 * @param string $deviceIp - The IP address of the device
 * @param int $channel - The channel number to set
 * @return bool - True if successful, false otherwise
 */
function setChannel($deviceIp, $channel) {
    try {
        $response = makeApiCall('POST', $deviceIp, 'command/channel', $channel, 'text/plain');
        $data = json_decode($response, true);
        return isset($data['data']) && $data['data'] === 'OK';
    } catch (Exception $e) {
        logMessage('Error setting channel: ' . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Function to check if a device supports volume control
 * 
 * @param string $deviceIp - The IP address of the device
 * @return bool - True if the device supports volume control, false otherwise
 */
function supportsVolumeControl($deviceIp) {
    try {
        $response = makeApiCall('GET', $deviceIp, 'details/device/model');
        $data = json_decode($response, true);
        $model = $data['data'] ?? '';
        return in_array($model, VOLUME_CONTROL_MODELS);
    } catch (Exception $e) {
        logMessage('Error checking volume control support: ' . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Function to sanitize and validate input data
 * 
 * @param mixed $data - The input data to sanitize
 * @param string $type - The type of data (e.g., 'int', 'ip')
 * @param array $options - Additional options for validation (e.g., min, max for integers)
 * @return mixed - The sanitized data, or null if invalid
 */
function sanitizeInput($data, $type, $options = []) {
    switch ($type) {
        case 'int':
            $sanitized = filter_var($data, FILTER_VALIDATE_INT, [
                'options' => [
                    'min_range' => $options['min'] ?? PHP_INT_MIN,
                    'max_range' => $options['max'] ?? PHP_INT_MAX
                ]
            ]);
            break;
        case 'ip':
            $sanitized = filter_var($data, FILTER_VALIDATE_IP);
            break;
        case 'string':
            $sanitized = filter_var($data, FILTER_SANITIZE_STRING);
            break;
        default:
            $sanitized = null;
    }
    return $sanitized !== false ? $sanitized : null;
}

/**
 * Function to log messages
 * 
 * @param string $message - The message to log
 * @param string $level - The log level (e.g., 'error', 'info')
 */
function logMessage($message, $level = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    error_log($formattedMessage, 3, __DIR__ . '/av_controls.log');
}

/**
 * Function to generate the HTML for a receiver form
 * 
 * @param string $receiverName - The name of the receiver
 * @param string $deviceIp - The IP address of the receiver
 * @param int $minVolume - The minimum volume level
 * @param int $maxVolume - The maximum volume level
 * @param int $volumeStep - The step size for the volume slider
 * @return string - The HTML for the receiver form
 */
function generateReceiverForm($receiverName, $deviceIp, $minVolume, $maxVolume, $volumeStep) {
    try {
        $currentChannel = getCurrentChannel($deviceIp);
        if ($currentChannel === null) {
            throw new Exception("Unable to get current channel");
        }
        $supportsVolume = supportsVolumeControl($deviceIp);
        
        $html = "<div class='receiver'>";
        $html .= "<form method='POST'>";
        $html .= "<button type='button' class='receiver-title'>" . htmlspecialchars($receiverName) . "</button>";
        
        // Generate channel selection dropdown
        $html .= "<label for='channel_" . htmlspecialchars($receiverName) . "'>Channel:</label>";
        $html .= "<select id='channel_" . htmlspecialchars($receiverName) . "' name='channel'>";
        foreach (TRANSMITTERS as $transmitterName => $channelNumber) {
            $selected = ($channelNumber == $currentChannel) ? ' selected' : '';
            $html .= "<option value='$channelNumber'$selected>" . htmlspecialchars($transmitterName) . "</option>";
        }
        $html .= "</select>";
        
        // Generate volume control if supported
        if ($supportsVolume) {
            $currentVolume = getCurrentVolume($deviceIp);
            if ($currentVolume === null) {
                $currentVolume = $minVolume;
            }
            $html .= "<label for='volume_" . htmlspecialchars($receiverName) . "'>Volume:</label>";
            $html .= "<input type='range' id='volume_" . htmlspecialchars($receiverName) . "' name='volume' min='$minVolume' max='$maxVolume' step='$volumeStep' value='$currentVolume' oninput='updateVolumeLabel(this)'>";
            $html .= "<span class='volume-label'>$currentVolume</span>";
        } else {
            $html .= "<p class='warning'>Volume control is not supported for this device.</p>";
        }
        
        $html .= "<input type='hidden' name='receiver_ip' value='" . htmlspecialchars($deviceIp) . "'>";
        $html .= "<button type='submit' class='update-button'>Update</button>";
        
        // Add power on and power off buttons
        $html .= "<div class='power-buttons'>";
        $html .= "<button type='button' class='power-on' onclick='sendPowerCommand(\"" . htmlspecialchars($deviceIp) . "\", \"cec_tv_on.sh\")'>Power On</button>";
        $html .= "<button type='button' class='power-off' onclick='sendPowerCommand(\"" . htmlspecialchars($deviceIp) . "\", \"cec_tv_off.sh\")'>Power Off</button>";
        $html .= "</div>";
        
        $html .= "</form>";
        $html .= "</div>";
    } catch (Exception $e) {
        logMessage("Error generating form for receiver {$receiverName}: " . $e->getMessage(), 'error');
        $html = "<div class='receiver error'>";
        $html .= "<h2>" . htmlspecialchars($receiverName) . "</h2>";
        $html .= "<p class='error-message'>Unable to reach " . htmlspecialchars($receiverName) . " (" . htmlspecialchars($deviceIp) . "). Please check that it is powered on and connected to the network.</p>";
        $html .= "</div>";
    }
    
    return $html;
}

/**
 * Function to send a power command to a specific receiver
 * 
 * @param string $deviceIp - The IP address of the receiver
 * @param string $command - The power command to send (e.g., 'cec_tv_on.sh' or 'cec_tv_off.sh')
 * @return array - An array containing the success status and message
 */
function sendPowerCommand($deviceIp, $command) {
    logMessage("Sending power command to $deviceIp: $command", 'debug');
    try {
        $response = makeApiCall('POST', $deviceIp, 'command/cli', $command, 'text/plain');
        $data = json_decode($response, true);
        $success = isset($data['data']) && $data['data'] === 'OK';
        logMessage("Power command response for $deviceIp: " . ($success ? 'Success' : 'Failure'), $success ? 'info' : 'warning');
        return [
            'success' => $success,
            'message' => $success ? "Power command sent successfully to $deviceIp." : "Failed to send power command to $deviceIp."
        ];
    } catch (Exception $e) {
        logMessage("Error sending power command to $deviceIp: " . $e->getMessage(), 'error');
        return [
            'success' => false,
            'message' => "Error sending power command to $deviceIp: " . $e->getMessage()
        ];
    }
}

/**
 * Function to send a power command to all receivers
 * 
 * @param string $command - The power command to send (e.g., 'cec_tv_on.sh' or 'cec_tv_off.sh')
 * @return array - An array of responses from each receiver
 */
function sendPowerCommandToAll($command) {
    $responses = [];
    foreach (RECEIVERS as $receiverName => $deviceIp) {
        $responses[$receiverName] = sendPowerCommand($deviceIp, $command);
    }
    return $responses;
}
