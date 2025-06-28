
<?php
session_start();

// Capture errors and previous inputs, if any
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

// Do not clear the whole session — just the used keys
unset($_SESSION['errors'], $_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration Page</title>
  <link rel="stylesheet" href="/Final_project/Views/Registration.css" />
  <script src="https://kit.fontawesome.com/8533c91442.js" crossorigin="anonymous"></script>
</head>

<body>
  <div class="formbox">
    <div class="formContainer">
      <div class="topheader">
        <h1>Register</h1>
      </div>

      <form action="../Controllers/registrationValidate.php" method="POST">
        <div class="form-group">
          <label for="name">Full Name:</label>
          <input type="text" name="name" class="inputText" placeholder="Full Name" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
          <span class="error"><?= $errors['name'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <label for="userid">User ID:</label>
          <input type="text" name="userid" class="inputText" placeholder="User ID" value="<?= htmlspecialchars($old['userid'] ?? '') ?>">
          <span class="error"><?= $errors['userid'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <label>Gender:</label>
          <div class="gender-options">
            <label><input type="radio" name="gender" value="male" <?= ($old['gender'] ?? '') === 'male' ? 'checked' : '' ?>> Male</label>
            <label><input type="radio" name="gender" value="female" <?= ($old['gender'] ?? '') === 'female' ? 'checked' : '' ?>> Female</label>
            <label><input type="radio" name="gender" value="other" <?= ($old['gender'] ?? '') === 'other' ? 'checked' : '' ?>> Other</label>
          </div>
          <span class="error"><?= $errors['gender'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <label for="phone">Phone Number:</label>
          <input type="text" name="phone" class="inputText" placeholder="Phone Number" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
          <span class="error"><?= $errors['phone'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <label for="gmail">Gmail:</label>
          <input type="email" name="gmail" class="inputText" placeholder="example@gmail.com" value="<?= htmlspecialchars($old['gmail'] ?? '') ?>">
          <span class="error"><?= $errors['gmail'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <label for="dob">Date of Birth:</label>
          <input type="date" name="dob" class="inputText" value="<?= htmlspecialchars($old['dob'] ?? '') ?>">
          <span class="error"><?= $errors['dob'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" name="password" class="inputText" placeholder="Password">
          <span class="error"><?= $errors['password'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <label for="repassword">Re-enter Password:</label>
          <input type="password" name="repassword" class="inputText" placeholder="Re-enter Password">
          <span class="error"><?= $errors['repassword'] ?? '' ?></span>
        </div>

        <div class="form-group">
          <button type="button" onclick="sendAuthCode()">Send OTP to Gmail</button>
          <input type="text" name="authcode" class="inputText" placeholder="Enter OTP to verify" value="<?= htmlspecialchars($old['authcode'] ?? '') ?>">
          <span class="error"><?= $errors['authcode'] ?? '' ?></span>
        </div>

        <div class="form-group full-width">
          <input type="submit" name="submitbtn" id="submitbtn" value="Register">
        </div>
      </form>

      <p>Already have an account? <a href="#login">Login Here</a></p>
    </div>
  </div>

  <script>
    function sendAuthCode() {
      const email = document.querySelector('input[name="gmail"]').value;

      if (!email) {
        alert('Please enter your Gmail first.');
        return;
      }

      fetch('../Controllers/sendAuthCode.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'gmail=' + encodeURIComponent(email)
      })
      .then(response => response.text())
      .then(data => {
        alert(data); // Show success/error message from PHP
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to send authentication code.');
      });
    }
  </script>
</body>
</html>
