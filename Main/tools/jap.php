<?php
/**
 * JustOS API Test Interface
 * 
 * This script provides a user interface for testing the JustOS API endpoints
 * on multiple devices. It includes enhanced debugging features and improved
 * response formatting.
 *
 * @version 1.4
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$CSV_FILE = "test.csv"; // Path to your CSV file

// Function to read CSV file
function readCsvFile($filename) {
    $endpoints = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        // Skip the header row
        fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $endpoints[] = [
                'category' => $data[0],
                'endpoint' => $data[1],
                'method' => $data[2],
                'description' => $data[3]
            ];
        }
        fclose($handle);
    }
    return $endpoints;
}

// Read endpoints from CSV
$API_ENDPOINTS = readCsvFile($CSV_FILE);

// Function to send API request
function sendApiRequest($url, $method, $payload = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    
    $headers = ['User-Agent: JustOS API Tester'];
    if ($method === 'POST' && $payload) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $headers[] = 'Content-Type: application/json';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $requestHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    $responseHeaders = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    $error = curl_error($ch);
    $errorNo = curl_errno($ch);
    curl_close($ch);
    
    return [
        'response' => $body,
        'httpCode' => $httpCode,
        'requestHeaders' => $requestHeaders,
        'responseHeaders' => $responseHeaders,
        'error' => $error,
        'errorNo' => $errorNo
    ];
}

// Function to format JSON
function formatJson($json) {
    $result = json_decode($json);
    return $result === null ? $json : json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

// Function to generate cURL command
function generateCurlCommand($url, $method, $payload = null) {
    $command = "curl -X $method";
    $command .= " -H 'User-Agent: JustOS API Tester'";
    if ($method === 'POST' && $payload) {
        $command .= " -H 'Content-Type: application/json'";
        $command .= " -d '" . addslashes($payload) . "'";
    }
    $command .= " '$url'";
    return $command;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deviceUrl = rtrim($_POST['device_url'], '/'); // Remove trailing slash if present
    $endpoint = $_POST['endpoint'];
    $method = $_POST['method'];
    $payload = ($_POST['payload']) ? $_POST['payload'] : null;
    
    $url = $deviceUrl . "/cgi-bin/api" . $endpoint;
    $result = sendApiRequest($url, $method, $payload);
    
    $responseMessage = "HTTP Code: " . $result['httpCode'] . "\n\n";
    $responseMessage .= "Response Headers:\n" . $result['responseHeaders'] . "\n";
    $responseMessage .= "Response Body:\n" . formatJson($result['response']);
    
    $requestDetails = "URL: " . $url . "\n";
    $requestDetails .= "Method: " . $method . "\n";
    $requestDetails .= "Request Headers:\n" . $result['requestHeaders'];
    if ($payload) {
        $requestDetails .= "Payload:\n" . formatJson($payload) . "\n";
    }
    if ($result['error']) {
        $requestDetails .= "cURL Error (" . $result['errorNo'] . "): " . $result['error'] . "\n";
    }

    // Generate cURL command
    $curlCommand = generateCurlCommand($url, $method, $payload);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JustOS API Test Interface</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a2e;
            color: #e2e8f0;
            background-image: linear-gradient(45deg, #1a1a2e 25%, #16213e 25%, #16213e 50%, #1a1a2e 50%, #1a1a2e 75%, #16213e 75%, #16213e 100%);
            background-size: 56.57px 56.57px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .input, .select, .textarea {
            width: 100%;
            padding: 0.75rem;
            background-color: rgba(26, 26, 46, 0.8);
            border: 1px solid #4a3f69;
            border-radius: 0.375rem;
            color: #f0e6ff;
            font-size: 0.875rem;
            transition: all 0.15s ease-in-out;
        }
        
        .input:focus, .select:focus, .textarea:focus {
            outline: none;
            border-color: #ff61d2;
            box-shadow: 0 0 0 3px rgba(255, 97, 210, 0.2);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.15s ease-in-out;
        }
        
        .btn-primary {
            background-color: #ff61d2;
            color: #16213e;
        }
        
        .btn-primary:hover {
            background-color: #ff00ff;
        }
        
        .response-area {
            background-color: rgba(26, 26, 46, 0.8);
            border: 1px solid #4a3f69;
            border-radius: 0.375rem;
            font-family: 'Fira Code', monospace;
            color: #00ffff;
            font-size: 0.875rem;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-x: auto;
        }
        
        .glow {
            text-shadow: 0 0 5px #ff61d2, 0 0 10px #ff61d2, 0 0 15px #ff61d2, 0 0 20px #ff61d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-4xl font-bold mb-8 text-center text-transparent bg-clip-text bg-gradient-to-r from-pink-500 via-purple-500 to-cyan-500 glow">JustOS API Test Interface</h1>
        
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="device_url" class="block text-sm font-medium text-purple-300 mb-2">Device URL</label>
                <input type="url" name="device_url" id="device_url" class="input" required placeholder="http://device-ip-address" value="<?php echo isset($_POST['device_url']) ? htmlspecialchars($_POST['device_url']) : ''; ?>">
            </div>

            <div>
                <label for="endpoint" class="block text-sm font-medium text-purple-300 mb-2">Endpoint</label>
                <select name="endpoint" id="endpoint" class="select" required>
                    <?php foreach ($API_ENDPOINTS as $api): ?>
                        <option value="<?php echo htmlspecialchars($api['endpoint']); ?>" data-method="<?php echo htmlspecialchars($api['method']); ?>">
                            <?php echo htmlspecialchars($api['category'] . ' - ' . $api['endpoint']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="method" class="block text-sm font-medium text-purple-300 mb-2">HTTP Method</label>
                <select name="method" id="method" class="select" required>
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                </select>
            </div>
            
            <div id="payloadDiv" style="display: none;">
                <label for="payload" class="block text-sm font-medium text-purple-300 mb-2">Payload (for POST requests)</label>
                <textarea name="payload" id="payload" class="textarea" rows="4"><?php echo isset($_POST['payload']) ? htmlspecialchars($_POST['payload']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary w-full">Send Request</button>
        </form>
        
        <?php if (isset($responseMessage)): ?>
            <div class="mt-8 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold mb-3 text-cyan-300">cURL Command</h2>
                    <pre class="response-area p-4 max-h-96"><?php echo htmlspecialchars($curlCommand); ?></pre>
                </div>
                <div>
                    <h2 class="text-lg font-semibold mb-3 text-cyan-300">Request Details</h2>
                    <pre class="response-area p-4 max-h-96"><?php echo htmlspecialchars($requestDetails); ?></pre>
                </div>
                <div>
                    <h2 class="text-lg font-semibold mb-3 text-cyan-300">Response</h2>
                    <pre class="response-area p-4 max-h-96"><?php echo htmlspecialchars($responseMessage); ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateMethodAndPayload() {
            var endpoint = document.getElementById('endpoint');
            var method = document.getElementById('method');
            var payloadDiv = document.getElementById('payloadDiv');
            var selectedOption = endpoint.options[endpoint.selectedIndex];
            
            // Set the method based on the endpoint's data-method attribute
            var allowedMethods = selectedOption.getAttribute('data-method').split('/');
            method.innerHTML = allowedMethods.map(m => `<option value="${m}">${m}</option>`).join('');
            
            // Show/hide payload based on whether POST is an allowed method
            payloadDiv.style.display = allowedMethods.includes('POST') ? 'block' : 'none';
        }

        document.getElementById('endpoint').addEventListener('change', updateMethodAndPayload);
        document.getElementById('method').addEventListener('change', function() {
            var payloadDiv = document.getElementById('payloadDiv');
            payloadDiv.style.display = this.value === 'POST' ? 'block' : 'none';
        });

        // Initial update on page load
        updateMethodAndPayload();
    </script>
</body>
</html>
