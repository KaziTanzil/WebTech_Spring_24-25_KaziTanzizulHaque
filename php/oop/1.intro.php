<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OOP-intro</title>
</head>
<body>
  <?php
    class calculator{
     public $a,$b,$c;

     function Sum()
     {
        $this->c= $this->a + $this->b;
        return $this->c;
     }
     function Sub()
     {
       $this->c= $this->a - $this->b;
        return $this->c;
     }

     function Mul()
     {
       $this->c= $this->a * $this->b;
        return $this->c;
     }

     function Div($a,$b)
     {
       
       $this->a=$a ;
       $this->b=$b;

       $this->c=$a/$b;
       
        return $this->c;
     }
    }
     $C1 = new calculator();
      $C1->a=10;
      $C1->b=15;
      echo"Sum = ".$C1->Sum();
      echo"<br>";

     $C2 = new calculator();
     $C2->a=20;
     $C2->b=7;
     echo"Sub = ".$C2->Sub();
    echo"<br>";

     $C3 = new calculator();
     $C3->a=30;
     $C3->b=9;
     echo"Multi = ".$C3->Sub();
     echo"<br>";

     $C4 = new calculator();
     echo"Division= ".$C4->Div(100,25)
     


  ?>
</body>
</html>