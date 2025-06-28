<?php
session_start();
require_once '../Database/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
    error_log("purchase.php: User not logged in");
    header('Location: ../Views/home.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

if (!$course_id) {
    error_log("purchase.php: No course ID provided");
    $error_message = 'No course selected.';
} else {
    error_log("purchase.php: course_id received - $course_id");
    try {
        $stmt = $conn->prepare("SELECT course_name, lecture_list, lecture_descriptions, category, duration, level, cost, file_path FROM courses WHERE course_id = ?");
        if (!$stmt) throw new Exception("Course query failed: " . $conn->error);
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$course) {
            error_log("purchase.php: Course not found - course_id=$course_id");
            $error_message = 'Course not found.';
        } else {
            error_log("purchase.php: Course data fetched - course_id=$course_id, data=" . json_encode($course));
        }
    } catch (Exception $e) {
        error_log("purchase.php: Error - " . $e->getMessage());
        $error_message = htmlspecialchars($e->getMessage());
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Course | LearnOnline Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Final_project/Views/userProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERet7yg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .purchase-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .purchase-container h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        .purchase-container p {
            margin: 0.5rem 0;
        }
        .error {
            color: red;
            margin-bottom: 1rem;
        }
        .success {
            color: green;
            margin-bottom: 1rem;
            font-weight: bold;
        }
        .purchase-form {
            margin-top: 1rem;
        }
        .payment-card-info {
            border: 1px solid #ddd;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .payment-card-info label {
            display: block;
            margin: 0.5rem 0 0.2rem;
            font-weight: 500;
        }
        .payment-card-info input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .payment-card-info .card-details {
            display: flex;
            gap: 1rem;
        }
        .payment-card-info .card-details input {
            width: 50%;
        }
        .purchase-form button {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .purchase-form button:hover {
            background-color: #0056b3;
        }
        .go-back {
            display: inline-block;
            margin-bottom: 1rem;
            color: #007bff;
            text-decoration: none;
        }
        .go-back:hover {
            text-decoration: underline;
        }
        .error-text {
            color: red;
            font-size: 0.8rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="purchase-container">
        <a href="/Final_project/Views/userProfile.php" class="go-back">← Back to Profile</a>
        <h2>Purchase Course</h2>
        <?php if ($success_message && $course && $course['file_path']): ?>
            <p class="success"><?php echo $success_message; ?></p>
            <script>
                console.log('purchase.php: Success message received, initiating download for course_id=<?php echo htmlspecialchars($course_id); ?>');
                window.onload = function() {
                    const filePath = '<?php echo htmlspecialchars($course['file_path']); ?>';
                    console.log('purchase.php: Attempting to trigger download for file: ' + filePath);
                    const link = document.createElement('a');
                    link.href = '/Final_project/Controllers/getCourseFile.php?course_id=<?php echo htmlspecialchars($course_id); ?>';
                    link.download = '<?php echo htmlspecialchars(basename($course['file_path'])); ?>';
                    link.onerror = function() {
                        console.error('purchase.php: Download failed for course_id=<?php echo htmlspecialchars($course_id); ?>');
                    };
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    console.log('purchase.php: Download triggered');
                };
            </script>
        <?php elseif ($success_message): ?>
            <p class="success"><?php echo $success_message; ?></p>
            <p>No downloadable material available for this course.</p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($course): ?>
            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
            <p><strong>Lectures:</strong> <?php echo htmlspecialchars($course['lecture_list'] ?? 'Not specified'); ?></p>
            <p><strong>Descriptions:</strong> <?php echo htmlspecialchars($course['lecture_descriptions'] ?? 'Not specified'); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($course['category'] ?? 'Not specified'); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($course['duration'] ?? 'Not specified'); ?> hours</p>
            <p><strong>Level:</strong> <?php echo htmlspecialchars($course['level'] ?? 'Not specified'); ?></p>
            <p><strong>Cost:</strong> $<?php echo htmlspecialchars(number_format($course['cost'], 2)); ?></p>
            <?php if ($course['file_path']): ?>
                <p><strong>Material:</strong> <?php echo htmlspecialchars($course['file_path']); ?> (Available after payment)</p>
            <?php else: ?>
                <p><strong>Material:</strong> No material available</p>
            <?php endif; ?>
            <form class="purchase-form" method="POST" action="/Final_project/Controllers/purchaseController.php" id="payment-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                <input type="hidden" name="amount" value="<?php echo htmlspecialchars($course['cost']); ?>">
                <div class="payment-card-info">
                    <h4>Payment Information</h4>
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                    <small id="card_number_error" class="error-text"></small>
                    <label for="card_holder">Cardholder Name</label>
                    <input type="text" id="card_holder" name="card_holder" placeholder="John Doe" required>
                    <small id="card_holder_error" class="error-text"></small>
                    <div class="card-details">
                        <div>
                            <label for="expiry_date">Expiry Date (MM/YY)</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                            <small id="expiry_date_error" class="error-text"></small>
                        </div>
                        <div>
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" required>
                            <small id="cvv_error" class="error-text"></small>
                        </div>
                    </div>
                </div>
                <button type="submit" name="submit" value="pay">Pay</button>
            </form>
            <script>
                document.getElementById('payment-form').addEventListener('submit', function(event) {
                    console.log('purchase.php: Form submission initiated');
                    let valid = true;
                    const cardNumber = document.getElementById('card_number').value.trim();
                    const cardHolder = document.getElementById('card_holder').value.trim();
                    const expiryDate = document.getElementById('expiry_date').value.trim();
                    const cvv = document.getElementById('cvv').value.trim();

                    // Clear previous error messages
                    document.querySelectorAll('.error-text').forEach(error => {
                        error.style.display = 'none';
                        error.textContent = '';
                    });

                    // Validate card number (16 digits)
                    if (!/^\d{16}$/.test(cardNumber.replace(/\s/g, ''))) {
                        document.getElementById('card_number_error').textContent = 'Card number must be 16 digits.';
                        document.getElementById('card_number_error').style.display = 'block';
                        valid = false;
                    }

                    // Validate cardholder name (non-empty)
                    if (!cardHolder) {
                        document.getElementById('card_holder_error').textContent = 'Cardholder name is required.';
                        document.getElementById('card_holder_error').style.display = 'block';
                        valid = false;
                    }

                    // Validate expiry date (MM/YY format, not expired)
                    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate)) {
                        document.getElementById('expiry_date_error').textContent = 'Expiry date must be MM/YY.';
                        document.getElementById('expiry_date_error').style.display = 'block';
                        valid = false;
                    } else {
                        const [month, year] = expiryDate.split('/').map(Number);
                        const currentDate = new Date();
                        const currentYear = currentDate.getFullYear() % 100;
                        const currentMonth = currentDate.getMonth() + 1;
                        if (year < currentYear || (year === currentYear && month < currentMonth)) {
                            document.getElementById('expiry_date_error').textContent = 'Card has expired.';
                            document.getElementById('expiry_date_error').style.display = 'block';
                            valid = false;
                        }
                    }

                    // Validate CVV (3-4 digits)
                    if (!/^\d{3,4}$/.test(cvv)) {
                        document.getElementById('cvv_error').textContent = 'CVV must be 3 or 4 digits.';
                        document.getElementById('cvv_error').style.display = 'block';
                        valid = false;
                    }

                    if (!valid) {
                        event.preventDefault();
                        console.log('purchase.php: Form validation failed');
                    } else {
                        console.log('purchase.php: Form validation passed, submitting to purchaseController.php');
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>