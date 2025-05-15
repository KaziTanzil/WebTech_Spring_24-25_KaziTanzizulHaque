<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Abstruct</title>
</head>
<body>

<?php
  abstract class calculator
  {
    abstract protected function cal($a , $b);
    
  }

  class B extends calculator{
      
    public function cal($x, $y)
    {
      $sum=$x + $y;

      echo $sum;
    }

  }

  $obj1=new B();
  $obj1->cal(5,5);
  


?>
  
</body>
</html>