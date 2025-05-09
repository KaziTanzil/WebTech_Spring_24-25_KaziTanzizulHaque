<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <?php
      $cars=array("Volvo","BMW","Toyota","Ford","Marcedes","Tesla");
      //array add item
    array_push($cars,"Nishan");
    
    foreach($cars as $x)
    {
      echo"$x<br>";
    }
    //array update
    $cars[0]="Ford";
     for($i=0;$i<count($cars);$i++)
    {
      print"$cars[$i]<br>";
    }
    echo"<br>";
    var_dump($cars);

   //associate array
    $student=["name"=>"Tanzil","CGPA"=>3.94,"DOB"=>1999,"ID"=>"22-47783-2"];
    //update
    $student["CGPA"]=4.00;
    //push or add
    $student["University"]="AIUB";
    foreach($student as $key => $val)
    {
      echo"$key : $val<br>";
    }
    

    $fruits=array("Apple","Banana","Water Milon","Mango","Queue","Guava","Grape");
    //sort
     echo"<br>";
     sort($fruits);
        print_r($fruits);

        //descending sort
        echo"<br>";
        rsort($fruits);
        print_r($fruits);
      



     echo"<br>";
     print_r($fruits);
   
    //array remove item
     echo"<br>";
    array_splice($fruits,0,2);
    print_r ($fruits);

    echo"<br>";

    unset($fruits[2]);
    print_r($fruits);

     //array pop
      echo"<br>";
     array_pop($fruits);
     print_r($fruits);
    
    //array remove first item
     echo"<br>";
    array_shift($fruits);
    print_r($fruits);

   
  ?>
</body>
</html>