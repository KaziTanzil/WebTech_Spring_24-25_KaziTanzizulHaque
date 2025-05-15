<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <?php
     class A{
        public $name="Tanzil";

        public function x()
        {
          echo "Hi this is base class";
        }

     }

     class B extends A
     {
       public $name="Tanzil Jr";

       public function x()
       {
         echo"Bye, this is derived class";
       }
     }

     $a1=new A();
     echo"<br>";
     echo $a1->name;
     echo"<br>";
     $a1->x();
     echo"<br>";

      $b1=new B();
     echo $b1->name;
     echo"<br>";
     $b1->x();

  ?>
</body>
</html>