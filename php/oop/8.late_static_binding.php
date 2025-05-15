<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Late_static</title>
</head>
<body>
  <?php
        class student{
          static $name="Tanzil, ";
          static function show()
          {
            echo"<br>Hello ".self::$name; 
          }
        }
        class s extends student{

          static $name="Neshat";
        }

    $s1=new s();
    $s1->show();// output will be "Tanzil" cause i use self:: in parent class

    

            class student1{
          static $name="Tanzil, ";
          static function show()
          {
            echo"<br>Hello ".static::$name; 
          }
        }
        class sss extends student1{

          static $name="Neshat";
        }

    $s2=new sss();
    $s2->show();// output will be update and show "Neshat" cause i use static:: in parent class...this is late static binding
  
  
  ?>
</body>
</html>