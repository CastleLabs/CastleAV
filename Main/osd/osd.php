<?php
session_start();

$message = '';

// Function to read and parse receivers file
function getReceivers() {
    $receivers = [];
    $file_path = __DIR__ . '/receivers.txt';

    if (file_exists($file_path)) {
        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Split on equals sign and handle optional spaces
            $parts = array_map('trim', explode('=', $line));
            if (count($parts) === 2) {
                $name = $parts[0];
                $ip = $parts[1];
                if (!empty($name) && !empty($ip)) {
                    $receivers[$ip] = $name;
                }
            }
        }
    }
    return $receivers;
}

// Common OSD presets
$presets = [
    'identify' => ['command' => 'osd -i', 'description' => 'Show IP and hostname on ALL receivers'],
    'identify_off' => ['command' => 'osd -i off', 'description' => 'Turn off identification display'],
    'debug' => ['command' => 'osd -d', 'description' => 'Show video resolution debug info'],
    'clear' => ['command' => 'osd -x', 'description' => 'Clear current OSD message'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ipAddress = $_POST['receiver'] ?? '';

    // Store settings in session
    $_SESSION['last_receiver'] = $_POST['receiver'] ?? '';
    $_SESSION['last_color'] = $_POST['color'] ?? '#FFFFFF';
    $_SESSION['last_size'] = $_POST['size'] ?? '175';
    $_SESSION['last_alignment'] = $_POST['alignment'] ?? 'center';
    $_SESSION['last_timeout'] = $_POST['timeout'] ?? '';

    // Handle preset commands
    if (isset($_POST['preset']) && array_key_exists($_POST['preset'], $presets)) {
        $command = $presets[$_POST['preset']]['command'];
        $_SESSION['last_preset'] = $_POST['preset'];
    } else {
        // Handle custom message command
        $string = $_POST['string'] ?? '';
        $color = $_POST['color'] ?? '';
        $size = $_POST['size'] ?? '';
        $timeout = $_POST['timeout'] ?? '';
        $alignment = $_POST['alignment'] ?? 'center';
        $command = '';

        // Build custom command
        if (!empty($string)) {
            $command = 'osd "' . addslashes($string) . '"';
            if (!empty($color)) {
                $command .= ' -c ' . ltrim($color, '#');
            }
            if (!empty($size)) {
                $command .= ' -s ' . $size;
            }
            if (!empty($alignment)) {
                $command .= ' -a ' . $alignment;
            }
            if (!empty($timeout)) {
                $command .= ' -t ' . $timeout;
            }
        }
        $_SESSION['last_preset'] = '';
    }

    // Validate and send command
    if (empty($ipAddress)) {
        $message = "Error: Receiver selection is required.";
    } elseif (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
        $message = "Error: Invalid IP Address in receiver selection.";
    } elseif (empty($command)) {
        $message = "Error: No command to send.";
    } else {
        $url = "http://{$ipAddress}/cgi-bin/api/command/cli";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $command);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: JustOS API Tester',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = "Error: " . curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode == 200) {
                $message = "Command sent successfully to $ipAddress!";
            } else {
                $message = "Error: Unexpected response (HTTP $httpCode)";
            }
        }

        curl_close($ch);
        $message .= "<br>Debug: Sent Command: " . htmlspecialchars($command);
        $message .= "<br>Debug: API Response: " . htmlspecialchars($response);
    }
}

$receivers = getReceivers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSD Command Sender</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --success: #059669;
            --error: #dc2626;
        }

        body {
            background-color: #0f172a;
            color: #e2e8f0;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .home-button {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .home-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .message {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .message.error {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .message.success {
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #e2e8f0;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: #e2e8f0;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        input[type="color"] {
            height: 42px;
            padding: 2px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
            color: white;
        }

        @media (max-width: 640px) {
            body {
                padding: 10px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OSD Command Sender</h1>
            <a href="http://192.168.8.127" class="home-button">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Home
            </a>
        </div>

        <div class="card">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo strpos($message, 'Error:') !== false ? 'error' : 'success'; ?>">
                    <?php if (strpos($message, 'Error:') !== false): ?>
                        <svg width="20" height="20" fill="none" stroke="var(--error)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    <?php else: ?>
                        <svg width="20" height="20" fill="none" stroke="var(--success)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    <?php endif; ?>
                    <div><?php echo $message; ?></div>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="receiver">Select Receiver</label>
                        <select id="receiver" name="receiver" required class="form-control">
                            <option value="">Choose a receiver...</option>
                            <?php foreach ($receivers as $ip => $name): ?>
                                <option value="<?php echo htmlspecialchars($ip); ?>" <?php echo (isset($_SESSION['last_receiver']) && $_SESSION['last_receiver'] === $ip) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="preset">Quick Actions</label>
                        <select id="preset" name="preset" class="form-control">
                            <option value="">Custom Message...</option>
                            <?php foreach ($presets as $key => $preset): ?>
                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo (isset($_SESSION['last_preset']) && $_SESSION['last_preset'] === $key) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($preset['description']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="custom-message-options">
                    <div class="form-group">
                        <label class="form-label" for="string">Message</label>
                        <textarea id="string" name="string" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="color">Text Color</label>
                            <input type="color" id="color" name="color" value="<?php echo $_SESSION['last_color'] ?? '#FFFFFF'; ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="size">Font Size (1-250)</label>
                            <input type="number" id="size" name="size" min="1" max="250" value="<?php echo $_SESSION['last_size'] ?? '125'; ?>" class="form-control">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="alignment">Alignment</label>
                            <select id="alignment" name="alignment" class="form-control">
                                <option value="center" <?php echo (!isset($_SESSION['last_alignment']) || $_SESSION['last_alignment'] === 'center') ? 'selected' : ''; ?>>Center</option>
                                <option value="left" <?php echo (isset($_SESSION['last_alignment']) && $_SESSION['last_alignment'] === 'left') ? 'selected' : ''; ?>>Left</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="timeout">Timeout (seconds)</label>
                            <input type="number" id="timeout" name="timeout" min="1" max="60" value="<?php echo $_SESSION['last_timeout'] ?? ''; ?>" placeholder="Leave empty for permanent" class="form-control">
                        </div>
                    </div>
                </div>

                <button type="submit">Send OSD Command</button>
