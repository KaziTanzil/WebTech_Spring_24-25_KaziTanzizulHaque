
<?php
$host = '127.0.0.1';
$dbname = 'project';
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    // Set charset to UTF-8
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Database connection failed: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database connection failed. Please try again later.']);
    exit;
}
?>
