<?php
/**
 * AV Controls for Just Add Power receivers - Main Script
 * 
 * This script handles the main logic for the AVcontrols project,
 * including form submission processing and rendering the user interface.
 *
 * @author Seth Morrow
 * @version 1.2
 * @date 2023-08-14
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Include configuration and utility files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    handleAjaxSubmission();
    exit;
}

// Include the HTML template
include __DIR__ . '/template.html';

/**
 * Handle AJAX form submission
 */
function handleAjaxSubmission() {
    $response = array('success' => false, 'message' => '');

    $selectedChannel = sanitizeInput($_POST['channel'], 'int');
    $deviceIp = sanitizeInput($_POST['receiver_ip'], 'ip');

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
            $response['message'] = "Error: " . $e->getMessage();
            logMessage("Error updating settings: " . $e->getMessage(), 'error');
        }
    } else {
        $response['message'] = "Invalid input data.";
        logMessage("Invalid input data received in POST request", 'error');
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

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
