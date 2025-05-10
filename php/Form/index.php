<?php
$username_err = "";
$id_err = "";
$username="";


if (isset($_POST["submit"])) {
  $username = trim($_POST["name"]);
  $username = htmlspecialchars($username);

  if (empty($username)) {
    $username_err = "Username must not be empty";
  } 
  elseif (!preg_match("/^[a-zA-Z]+$/", $username)) {
    $username_err = "Username must contain letters only";
  }

  $id = trim($_POST["id"]);
  $id = htmlspecialchars($id);

  if (empty($id)) {
    $id_err = "ID must not be empty";
  }
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
  <h1>This is a form that validate by php</h1>

  <form method="post">
    <table>
      <tr>
        <td>Name</td>
        <td><input type="text" name="name" value="<?php echo htmlspecialchars($username) ?>" placeholder="Enter your name"></td>
        <td><span style="color:red;"><?php echo$username_err ?></span></td>
      </tr>

      <tr>
        <td>ID</td>
        <td><input type="text" name="id" value="<?php echo htmlspecialchars($id)?>"></td>
        <td><span style="color:red;"><?php echo$id_err ?></span></td>
      </tr>
      <tr>
      <tr>
        <td>Email</td>
        <td><input type="email" name="email"></td>
        
      </tr>
       <tr>
          <td  colspan="2"> <input type="submit" name="submit" id=""></td>
          
       </tr>
    </table>
  </form>



</body>
</html>