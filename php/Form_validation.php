<?php

$username_err = $Age_err = $email_err = $gender_err = $language_err = $dish_err = "";
$username = $Age = $email = $gender = $language = "";
$dish = [];

$has_error = false;

if (isset($_POST["submit"])) {
    $username = htmlspecialchars(trim($_POST["name"]));

    if (empty($username)) {
        $username_err = "Username must not be empty";
        $has_error = true;
    } 
    elseif (!preg_match("/^[a-zA-Z ]+$/", $username)) {
        $username_err = "Username must contain letters only";
        $has_error = true;
    }

    $Age = htmlspecialchars(trim($_POST["Age"]));
    if (empty($Age)) {
        $Age_err = "Age must not be empty";
    } 
    elseif (!preg_match("/^[0-9]+$/", $Age)) {
        $Age_err = "Age must contain numbers only";
        $has_error = true;
    }

    $email = htmlspecialchars(trim($_POST["email"]));
    if (empty($email)) {
        $email_err = "Email must not be empty";
        $has_error = true;
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format";
        $has_error = true;
    }

    $gender = isset($_POST["gender"]) ? $_POST["gender"] : "";
    if (empty($gender)) {
        $gender_err = "Gender is required";
        $has_error = true;
    }

    $language = isset($_POST["language"]) ? $_POST["language"] : "";
    if (empty($language)) {
        $language_err = "Favorite language is required";
        $has_error = true;
    }


    if (isset($_POST["dish"])) {
        $dish = $_POST["dish"];
    } 
    else {
        $dish_err = "Please select at least one favorite dish";
        $has_error = true;
    }

    if (!$has_error) {
        echo "<h1>Validation Successful</h1>";
    }
}

if (isset($_POST["reset"])) {
    $username_err = $Age_err = $email_err = $gender_err = $language_err = $dish_err = "";
    $username = $Age = $email = $gender = $language = "";
    $dish = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <h1>This is a form that validates by PHP</h1>

  <form method="post">
    <table>
      <tr>
        <td>Name</td>
        <td><input type="text" name="name" value="<?php echo htmlspecialchars($username) ?>" placeholder="Enter your name"></td>
        <td><span style="color:red;"><?php echo $username_err ?></span></td>
      </tr>

      <tr>
        <td>Age</td>
        <td><input type="text" name="Age" value="<?php echo htmlspecialchars($Age)?>" placeholder="Enter your Age"></td>
        <td><span style="color:red;"><?php echo $Age_err ?></span></td>
      </tr>

      <tr>
        <td>Email</td>
        <td><input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"></td>
        <td><span style="color:red;"><?php echo $email_err; ?></span></td>
      </tr>

      <tr>
        <td>Gender</td>
        <td>
          <input type="radio" name="gender" value="Male" <?php if ($gender == "Male") echo "checked"; ?>> Male
          <input type="radio" name="gender" value="Female" <?php if ($gender == "Female") echo "checked"; ?>> Female
          <input type="radio" name="gender" value="Other" <?php if ($gender == "Other") echo "checked"; ?>> Other
        </td>
        <td><span style="color:red;"><?php echo $gender_err; ?></span></td>
      </tr>


      <tr>
        <td>Favorite Dish</td>
        <td>
          <input type="checkbox" name="dish[]" value="Pizza" <?php if (isset($_POST['dish']) && in_array('Pizza', $_POST['dish'])) echo "checked"; ?>> Pizza
          <input type="checkbox" name="dish[]" value="Burger" <?php if (isset($_POST['dish']) && in_array('Burger', $_POST['dish'])) echo "checked"; ?>> Burger
          <input type="checkbox" name="dish[]" value="Pasta" <?php if (isset($_POST['dish']) && in_array('Pasta', $_POST['dish'])) echo "checked"; ?>> Pasta
          <input type="checkbox" name="dish[]" value="Sushi" <?php if (isset($_POST['dish']) && in_array('Sushi', $_POST['dish'])) echo "checked"; ?>> Sushi
        </td>
        <td><span style="color:red;"><?php echo $dish_err; ?></span></td>
      </tr>


      <tr>
        <td>Favorite Language</td>
        <td>
          <select name="language">
            <option value="">Select Language</option>
            <option value="C++" <?php if ($language == "C++") echo "selected"; ?>>C++</option>
            <option value="Java" <?php if ($language == "Java") echo "selected"; ?>>Java</option>
            <option value="Python" <?php if ($language == "Python") echo "selected"; ?>>Python</option>
            <option value="PHP" <?php if ($language == "PHP") echo "selected"; ?>>PHP</option>
            <option value="JavaScript" <?php if ($language == "JavaScript") echo "selected"; ?>>JavaScript</option>
          </select>
        </td>
        <td><span style="color:red;"><?php echo $language_err; ?></span></td>
      </tr>

      <tr>
        <td><input type="submit" name="submit" value="Submit"></td>
        <td><input type="submit" name="reset" value="Reset"></td>
      </tr>
    </table>
  </form>

</body>
</html>
