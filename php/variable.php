<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <div >
    <h1>My name is Tanzil</h1>
  </div>

  <?php
    echo"<br> I am 25 years old";
    $color="Black";
    $Color="Brown";
    $num1=10;
    $num2=20;
    $isActive=true;
    $inactive=false;

    echo "<br> My hair color is ".$color;
    echo "<br>My eye color is ".$Color;
    echo"<br>".$num1;
    echo"<br> sum = ".$num1+$num2;
    echo"<br> A ".$color." haired boy with ".$Color." eyes.";
    echo"<br>";
    echo"Hello".$isActive;
    echo"<br>";
    echo"Hello".$inactive;
    echo"<br>";
    var_dump($color);
    echo"<br>";
    var_dump($num1);
    echo"<br>";
    var_dump($inactive);

    $x=$y=$z=50;

    echo'<br>'.$x;
    echo"<br>$z";

    echo"<br> A $color haired boy with $Color eyes.";


    $cars=array("Volvo","BMW","Toyota","Ford","Marcedes","Tesla");
    array_push($cars,"Nishan");
    foreach($cars as $x)
    {
      echo"$x<br>";
    }
     



    //global variable
     $a=10;
    function myFunc()
    {
      global $a;
     
      $b=20;
      echo"<br>Global variable is $a";
      echo"<br>local variable is $b";
    }

    myFunc();
    echo"<br>Global variable is $a";
    echo"<br>You cant call local variable from another block of code b";


  ?>
</body>
</html>