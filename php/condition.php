<?php
$lol = "";
$A = "";
$B = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $A = trim($_POST["A_val"]);
    $B = trim($_POST["B_val"]);

    if (empty($A) || empty($B)) {
        $lol = "Please enter both values.";
    } elseif ($A > $B) {
        $lol = "A is greater than B";
    } elseif ($A < $B) {
        $lol = "B is greater than A";
    } else {
        $lol = "A is equal to B";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Condition</title>
</head>
<body>
  <form method="POST">
    <label>Value of A =
      <input type="number" name="A_val" value="<?php echo htmlspecialchars($A); ?>" required>
      <span><?php echo "Value of A = " . $A; ?></span>
    </label>
    <br>
    <label>Value of B =
      <input type="number" name="B_val" value="<?php echo htmlspecialchars($B); ?>" required>
      <span><?php echo "Value of B = " . $B; ?></span>
    </label>
    <br>
    <input type="submit" >
    <br><br>
    <span><strong><?php echo $lol; ?></strong></span>
  </form>
</body>
</html>
