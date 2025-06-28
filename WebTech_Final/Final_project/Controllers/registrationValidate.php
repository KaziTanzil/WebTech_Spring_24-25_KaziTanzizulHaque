<?php
session_start();
require_once '../Database/db.php';
require_once '../Models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $userid = trim($_POST['userid'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $gmail = trim($_POST['gmail'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $password = $_POST['password'] ?? '';
    $repassword = $_POST['repassword'] ?? '';
    $authcode = trim($_POST['authcode'] ?? '');

    $errors = [];
    $old = [
      'name' => $name,
      'userid' => $userid,
      'gender' => $gender,
      'phone' => $phone,
      'gmail' => $gmail,
      'dob' => $dob,
      'authcode' => $authcode,
    ];

    // Name: only letters and spaces
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors['name'] = "Name must contain only letters and spaces.";
    }

    if (empty($userid)) {
        $errors['userid'] = "User ID is required.";
    }

    if (empty($gender)) {
        $errors['gender'] = "Please select your gender.";
    }

    // Phone number: Bangladesh format
    // Typical Bangladesh phone numbers start with +880 or 0 followed by 10 digits
    if (empty($phone)) {
        $errors['phone'] = "Phone number is required.";
    } elseif (!preg_match("/^(?:\+8801|01)[3-9]\d{8}$/", $phone)) {
        $errors['phone'] = "Invalid Bangladeshi phone number.";
    }

    // Gmail validation with regex (basic)
    if (empty($gmail)) {
        $errors['gmail'] = "Gmail is required.";
    } elseif (!filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        $errors['gmail'] = "Invalid email format.";
    } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $gmail)) {
        $errors['gmail'] = "Email must be a Gmail address.";
    }

    // Date of birth validation (between 1920 and 2015)
    if (empty($dob)) {
        $errors['dob'] = "Date of birth is required.";
    } else {
        $year = (int)date('Y', strtotime($dob));
        if ($year < 1920 || $year > 2015) {
            $errors['dob'] = "Date of birth must valid(minimun age requirement 10 years";
        }
    }

    // Password validations:
    // Length between 8 and 22
    // Must contain at least one letter, one number, and one special character
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } else {
        if (strlen($password) < 8 || strlen($password) > 22) {
            $errors['password'] = "Password must be between 8 and 22 characters.";
        }
        if (!preg_match('/[A-Za-z]/', $password)) {
            $errors['password'] = "Password must contain at least one letter.";
        }
        if (!preg_match('/\d/', $password)) {
            $errors['password'] = "Password must contain at least one number.";
        }
        if (!preg_match('/[\W_]/', $password)) {
            $errors['password'] = "Password must contain at least one special character.";
        }
    }

    if ($password !== $repassword) {
        $errors['repassword'] = "Passwords do not match.";
    }

    if (empty($authcode)) {
        $errors['authcode'] = "Authentication code is required.";
    }

    // Check if user exists
    $userModel = new User($conn);
    if ($userModel->exists('ID', $userid)) {
        $errors['userid'] = "User ID already taken.";
    }
    if ($userModel->exists('Gmail', $gmail)) {
        $errors['gmail'] = "Email already registered.";
    }
    if ($userModel->exists('PhoneNumber', $phone)) {
        $errors['phone'] = "Phone number already registered.";
    }

    // Check authcode matches session stored
    if (!isset($_SESSION['authcode']) || $authcode !== $_SESSION['authcode']) {
        $errors['authcode'] = "Invalid authentication code.";
    }

    if ($errors) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $old;
        header('Location: ../Views/Registration.php');
        exit();
    }

    // Prepare user data to save (password stored as plain text)
    $userData = [
        'userid' => $userid,
        'name' => $name,
        'gender' => $gender,
        'phone' => $phone,
        'gmail' => $gmail,
        'dob' => $dob,
        'password' => $password  // plain text password saved here
    ];

    $result = $userModel->save($userData);

    if ($result['success']) {
        // Clear authcode session on success
        unset($_SESSION['authcode']);
        header('Location: ../Views/home.php'); // Redirect to home or login page
        exit();
    } else {
        $_SESSION['errors'] = ['general' => "Registration failed: " . $result['error']];
        $_SESSION['old'] = $old;
        header('Location: ../Views/Registration.php');
        exit();
    }
} else {
    header('Location: ../Views/Registration.php');
    exit();
}
