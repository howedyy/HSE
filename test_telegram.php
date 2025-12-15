<?php
// Simple test script for Telegram API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Telegram configuration
$botToken = "7790658256:AAFeCjdZ_IllGGp1_5IE4A7P1eSoLCcWgHk";
$chatId = -1002792075991; // Hse-test

// Test message
$message = "🧪 This is a test message from XAMPP at " . date('Y-m-d H:i:s');

echo "<h1>Telegram API Test</h1>";

// First check connectivity to Telegram API
echo "<h2>Testing connectivity...</h2>";
$internetTest = @fsockopen("api.telegram.org", 443, $errno, $errstr, 5); 
if(!$internetTest) {
    echo "<p style='color:red'>❌ Cannot connect to api.telegram.org - Error: $errstr ($errno)</p>";
} else {
    echo "<p style='color:green'>✅ Successfully connected to api.telegram.org</p>";
    fclose($internetTest);
}

// Function to test Telegram API
function sendTelegramMessage($message, $botToken, $chatId) {
    echo "<h2>Attempting to send message...</h2>";
    echo "<p>Bot Token (first 10 chars): " . substr($botToken, 0, 10) . "...</p>";
    echo "<p>Chat ID: $chatId</p>";
    echo "<p>Message: $message</p>";
    
    // Check if cURL is available
    if (!function_exists('curl_init')) {
        echo "<p style='color:orange'>⚠️ cURL is not available, using file_get_contents instead</p>";
        
        // Try file_get_contents
        $url = "https://api.telegram.org/bot$botToken/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message
        ];
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 5
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            echo "<p style='color:red'>❌ file_get_contents failed</p>";
            echo "<p>Error: " . error_get_last()['message'] . "</p>";
            return false;
        }
        
        echo "<p style='color:green'>✅ Message sent successfully using file_get_contents</p>";
        echo "<p>Response: " . htmlspecialchars($result) . "</p>";
        return true;
    }
    
    // Use cURL
    echo "<p>Using cURL to send message...</p>";
    $ch = curl_init("https://api.telegram.org/bot$botToken/sendMessage");
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'chat_id' => $chatId,
        'text' => $message
    ]);
    
    // Enable verbose output
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    
    // Get verbose information
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    echo "<h3>cURL Results:</h3>";
    if ($response === false) {
        echo "<p style='color:red'>❌ cURL request failed</p>";
        echo "<p>Error #$errno: " . htmlspecialchars($error) . "</p>";
    } else {
        echo "<p>HTTP Status Code: $status</p>";
        echo "<p>Response: " . htmlspecialchars($response) . "</p>";
        
        if ($status >= 200 && $status < 300) {
            echo "<p style='color:green'>✅ Message sent successfully using cURL</p>";
        } else {
            echo "<p style='color:red'>❌ API returned non-success status code: $status</p>";
        }
    }
    
    echo "<h4>Verbose Log:</h4>";
    echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";
    
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

// Try sending a test message
$result = sendTelegramMessage($message, $botToken, $chatId);

// Check getmypid
echo "<h2>Server Information:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server IP: " . $_SERVER['SERVER_ADDR'] . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Enabled' : 'Disabled') . "</p>";

// Test Telegram API directly
echo "<h2>Testing Direct API Access:</h2>";
echo "<p>Checking https://api.telegram.org/bot{token}/getMe...</p>";

$testUrl = "https://api.telegram.org/bot$botToken/getMe";
$testContext = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);

$testResult = @file_get_contents($testUrl, false, $testContext);
if ($testResult === FALSE) {
    echo "<p style='color:red'>❌ Cannot access Telegram API directly. Error: " . error_get_last()['message'] . "</p>";
} else {
    echo "<p style='color:green'>✅ Successfully accessed Telegram API</p>";
    echo "<p>Response: " . htmlspecialchars($testResult) . "</p>";
}

// Provide recommendations
echo "<h2>Recommendations:</h2>";
if (!$internetTest) {
    echo "<p>Your server cannot connect to api.telegram.org. This could be due to:</p>";
    echo "<ul>";
    echo "<li>Firewall blocking outgoing connections</li>";
    echo "<li>Network configuration issues</li>";
    echo "<li>DNS resolution problems</li>";
    echo "</ul>";
    echo "<p>Try temporarily disabling your firewall or checking your network settings.</p>";
} elseif (strpos($botToken, ":") === false) {
    echo "<p>Your bot token appears to be invalid. A valid token should be in format '123456789:ABCDefgh-ijklmnopQRSTUvwxyz'</p>";
} elseif (!is_numeric($chatId)) {
    echo "<p>Your chat ID should be numeric. For group chats it should be a negative number.</p>";
} else {
    echo "<p>If you're still having issues, consider:</p>";
    echo "<ul>";
    echo "<li>Check if the bot is a member of the group</li>";
    echo "<li>Verify the bot has permission to send messages</li>";
    echo "<li>Try with a different bot token or chat ID</li>";
    echo "</ul>";
}
?>