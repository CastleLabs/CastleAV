<?php
/**
 * AV Controls for Just Add Power receivers - Main Script
 *
 * This script handles the main logic for the AVcontrols project,
 * including form submission processing and rendering the user interface.
 *
 * @author Seth Morrow
 * @version 1.6
 * @date 2023-09-24
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

/**
 * Generate remote control HTML
 *
 * @return string The HTML for the remote control
 */
function generateRemoteControl() {
    $html = <<<HTML
    <div class="remote-container">
        <h2>Remote Control</h2>
        <div id="transmitter-select">Select Transmitter: Loading transmitters...</div>
        <div class="remote">
            <div class="button-row">
                <button onclick="sendCommand('power')">Power</button>
                <button onclick="sendCommand('guide')">Guide</button>
            </div>
            <div class="navigation-pad">
                <button onclick="sendCommand('up')">▲</button>
                <div class="nav-row">
                    <button onclick="sendCommand('left')">◀</button>
                    <button onclick="sendCommand('select')">OK</button>
                    <button onclick="sendCommand('right')">▶</button>
                </div>
                <button onclick="sendCommand('down')">▼</button>
            </div>
            <div class="button-row">
                <button onclick="sendCommand('channel_up')">CH +</button>
                <button onclick="sendCommand('channel_down')">CH -</button>
            </div>
            <div class="number-pad">
                <button onclick="sendCommand('1')">1</button>
                <button onclick="sendCommand('2')">2</button>
                <button onclick="sendCommand('3')">3</button>
                <button onclick="sendCommand('4')">4</button>
                <button onclick="sendCommand('5')">5</button>
                <button onclick="sendCommand('6')">6</button>
                <button onclick="sendCommand('7')">7</button>
                <button onclick="sendCommand('8')">8</button>
                <button onclick="sendCommand('9')">9</button>
                <button onclick="sendCommand('last')">Last</button>
                <button onclick="sendCommand('0')">0</button>
                <button onclick="sendCommand('exit')">Exit</button>
            </div>
        </div>
    </div>
    <div id="error-message" class="error-message">
        <strong>Error!</strong> <span id="error-text"></span>
    </div>
HTML;

    return $html;
}

// Start output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bowling Bar AV Controls</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <style>
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: linear-gradient(to right, #7c3aed, #4f46e5);
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }

        .logo-title-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .home-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .home-button:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .logo-container {
            margin: 0;
        }

        h1 {
            margin: 0;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="logo-title-group">
            <div class="logo-container">
                <img src="logo.png" alt="Castle AV Controls Logo" class="logo">
            </div>
            <h1>Bowling Bar AV Controls</h1>
        </div>
        <a href="<?php echo HOME_URL; ?>" class="home-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            Home
        </a>
    </div>

    <div class="receivers-wrapper">
        <?php echo generateReceiverForms(); ?>
    </div>

    <?php echo generateRemoteControl(); ?>

    <div id="response-message"></div>
</body>
</html>

<?php
// Get the buffered content and clean the buffer
$content = ob_get_clean();

// Output the content
echo $content;
?>
