<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Traits</title>
</head>
<body>

<?php
  trait text{

    function x()
    {
      echo"This is very important <br>";
    }
  }

    trait text1{

    function y()
    {
      echo"This is not importent <br>";
    }
  }


  class A{
    use text;
  }

   class B{
    use text,text1;
  }

   class C{
    use text;
  }

  $a1=new A();
  $a1->x();

  $b1=new B();
  $b1->x();
  $b1->y();


  $c1=new C();
  $c1->x();


?>
  
</body>
</html>