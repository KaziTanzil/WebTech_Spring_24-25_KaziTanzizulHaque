<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Interface</title>
</head>
<body>
  <?php
    interface A{
      function f1($f1);
    }
     interface B{
      function f2($f2);
      function f3($f3);
    }

    class C implements A,B{

      public function f1($f1)
      {
        echo "hello ".$f1;
      }
       public function f2($f2)
      {
        echo "hi ".$f2;
      }
       public function f3($f3)
      {
        echo "bye".$f3;
      }
    }

    $obj1= new C();
    $obj1->f1(4);
    echo"<br>";
    $obj1->f2(10);
    echo"<br>";
    $obj1->f3(7);

  ?>
</body>
</html>