<?php
/**
 * AV Controls for Just Add Power receivers - Main Script
 * 
 * This script handles the main logic for the AVcontrols project,
 * including form submission processing and rendering the user interface.
 *
 * @author Seth Morrow
 * @version 1.4
 * @date 2023-09-03
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering
ob_start();

// Include configuration and utility files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    logMessage("AJAX request received. POST data: " . print_r($_POST, true), 'debug');
    
    $response = array('success' => false, 'message' => '');

    $deviceIp = sanitizeInput($_POST['receiver_ip'], 'ip');

    if (isset($_POST['power_command'])) {
        // Handle power command
        $powerCommand = sanitizeInput($_POST['power_command'], 'string');
        try {
            $commandResponse = makeApiCall('POST', $deviceIp, 'command/cli', $powerCommand, 'text/plain');
            $responseData = json_decode($commandResponse, true);
            if (isset($responseData['data']) && $responseData['data'] === 'OK') {
                $response['success'] = true;
                $response['message'] = "Power command sent successfully.";
            } else {
                $response['message'] = "Error sending power command: Unexpected response.";
            }
        } catch (Exception $e) {
            $response['message'] = "Error sending power command: " . $e->getMessage();
            logMessage("Error sending power command: " . $e->getMessage(), 'error');
        }
    } else {
        // Handle channel and volume update
        $selectedChannel = sanitizeInput($_POST['channel'], 'int');

        if ($selectedChannel && $deviceIp) {
            try {
                $channelResponse = setChannel($deviceIp, $selectedChannel);
                $response['message'] .= "Channel: " . ($channelResponse ? "Successfully updated" : "Update failed") . "\n";

                if (supportsVolumeControl($deviceIp)) {
                    $selectedVolume = sanitizeInput($_POST['volume'], 'int', ['min' => MIN_VOLUME, 'max' => MAX_VOLUME]);
                    if ($selectedVolume) {
                        $volumeResponse = setVolume($deviceIp, $selectedVolume);
                        $response['message'] .= "Volume: " . ($volumeResponse ? "Successfully updated" : "Update failed") . "\n";
                    }
                }

                $response['success'] = true;
            } catch (Exception $e) {
                $response['message'] = "Error updating settings: " . $e->getMessage();
                logMessage("Error updating settings: " . $e->getMessage(), 'error');
            }
        } else {
            $response['message'] = "Invalid input data.";
            logMessage("Invalid input data received in POST request", 'error');
        }
    }

    logMessage("Sending JSON response: " . json_encode($response), 'debug');
    ob_end_clean(); // Clear any output
    header('Content-Type: application/json');
    echo json_encode($response, JSON_INVALID_UTF8_IGNORE);
    exit;
}

// Include the HTML template
include __DIR__ . '/template.html';

/**
 * Generate receiver forms HTML
 *
 * @return string The HTML for all receiver forms
 */
function generateReceiverForms() {
    $html = '';
    foreach (RECEIVERS as $receiverName => $deviceIp) {
        try {
            $html .= generateReceiverForm($receiverName, $deviceIp, MIN_VOLUME, MAX_VOLUME, VOLUME_STEP);
        } catch (Exception $e) {
            $html .= "<div class='receiver'><p class='warning'>Error generating form for " . htmlspecialchars($receiverName) . ": " . htmlspecialchars($e->getMessage()) . "</p></div>";
            logMessage("Error generating form for {$receiverName}: " . $e->getMessage(), 'error');
        }
    }
    return $html;
}

// Check if any receivers are reachable
$allReceiversUnreachable = true;
foreach (RECEIVERS as $receiverName => $deviceIp) {
    try {
        getCurrentChannel($deviceIp);
        $allReceiversUnreachable = false;
        break;
    } catch (Exception $e) {
        continue;
    }
}

// If all receivers are unreachable, display a global error message
if ($allReceiversUnreachable) {
    echo "<div class='global-error'>" . ERROR_MESSAGES['global'] . "</div>";
}

ob_end_flush();
