<?php
 //require "database.php"    here If file.php doesn't exist, script stops
 include 'database.php'; // If file.php doesn't exist, script continues


$obj =new database();

$obj->insert("student",["id"=>6, "student_name"=>"Tanzil","age"=>25,"city"=>"dhaka"]);
echo"Insert result is : ";
print_r($obj->getResult());
?>