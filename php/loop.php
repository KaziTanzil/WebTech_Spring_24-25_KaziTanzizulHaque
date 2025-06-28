<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>loop</title>
</head>
<body>
  <?php
  //forloop
  for($i=0;$i<10;$i++)
    {
      echo"Hello world".$i;
      echo"<br>";
    }

  ?>
   <?php
   //nested for loop

   for($i=0;$i<=5;$i++)
    {
      for($j=0;$j<=5-$i;$j++)
      {
        echo"*";
      }
      echo"<br>";
    }

   ?>
   <?php
   
    //do while loop
    $i=0;
    do {
      echo $i;
      $i++;
    } while ($i < 6);
   
   ?>

</body>
</html>