
<?php
require_once '../Database/db.php';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/Logs/php_errors.log');

if (!isset($_GET['user_id']) || empty(trim($_GET['user_id'])) || !ctype_digit(trim($_GET['user_id']))) {
    error_log("[" . date('Y-m-d H:i:s') . "] Invalid or missing user_id in getImage.php: " . ($_GET['user_id'] ?? 'null'));
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$user_id = trim($_GET['user_id']);
error_log("[" . date('Y-m-d H:i:s') . "] getImage.php user_id: $user_id");

try {
    $stmt = $conn->prepare("SELECT ProfilePic FROM userProfile WHERE ID = ?");
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    $stmt->bind_result($profilePic);
    $fetched = $stmt->fetch();
    $stmt->close();

    if (!$fetched || $profilePic === null || strlen($profilePic) === 0) {
        error_log("[" . date('Y-m-d H:i:s') . "] No valid profile image found for user_id: $user_id");
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($profilePic);
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!$mime || !in_array($mime, $allowed_mimes)) {
        error_log("[" . date('Y-m-d H:i:s') . "] Invalid MIME type: " . ($mime ?: 'unknown') . " for user_id: $user_id");
        header('HTTP/1.1 415 Unsupported Media Type');
        exit;
    }

    header("Content-Type: $mime");
    header("Content-Length: " . strlen($profilePic));
    header("Cache-Control: no-cache, must-revalidate");
    ob_clean(); // Clear output buffer
    echo $profilePic;
    error_log("[" . date('Y-m-d H:i:s') . "] Profile image served for user_id: $user_id, MIME: $mime, Size: " . strlen($profilePic));
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Error in getImage.php: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    exit;
}
?>