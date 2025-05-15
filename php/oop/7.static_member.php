<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Static Member</title>
</head>
<body>
   <?php

   //if all members and methos are static then the class would be called static
    class student{
      static $name="Tanzil, ";
      static function show()
      {
        echo"Hello ".self::$name;
      }

      public function __construct()
      {
        echo"<br>";
        self::show();
      }
    }
    echo student::$name;
    //student::show();

    $s1=new student();

    echo"<br>";






    class student1{
      static $name="Tanzil";
    }

    class acc extends student1{
      public function show()
      {
        echo "hello".parent::$name;
      }
    }

    $obj1= new acc();
    $obj1->show();



    

    //update value
        class student3{
      static $name="Tanzil, ";
      static function show()
      {
        echo"Hello ".self::$name;
      }

      public function __construct($n)
      {
        echo"<br>";
        self::$name=$n;
      }
    }
    
    $s1=new student3("bokac*d*");
    $s1->show();

   ?>
</body>
</html>