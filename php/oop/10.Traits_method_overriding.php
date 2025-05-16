<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trait Method overriding</title>
</head>
<body>
  <?php
    trait test{
      function hello()
      {
        echo"hello, This is trait";     //priority-2
      }
    }

    trait test1{
            private function hi()                 //private function in trait
      {
        echo"<br>This is a private trait function";
      }
    }

    class A{
              function hello()
      {
        echo"hi, This is Parent";
      }
    }

    class B extends A{
      use test;
            function hello()        //priority-1
      {
        echo"Bye, This is Child";
      }

      use test1{
        test1::hi as public newHi;   //private function of trait =>>> public and set a new name of it "newHi" or anything
      }


    }

    $obj1= new B();
    $obj1->hello();
    $obj1->newHi();

?>
</body>
</html>