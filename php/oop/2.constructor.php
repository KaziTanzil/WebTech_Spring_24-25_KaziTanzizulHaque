<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Construct and dectruct</title>
</head>
<body>
  <?php
    class person{
      public $name;
      public $Age;
      public $Eye_color;

      function __construct($name,$Age,$Eye_color)
      {
        $this->name=$name;
        $this->Age=$Age;
        $this->Eye_color=$Eye_color;
      }

      function details()
      {
        Echo"Name : ".$this->name;
        Echo"<br>Age : ".$this->Age;
        Echo"<br>Eye Color : ".$this->Eye_color;
        echo"<br>";
      }


    }
$p1= new person("Tanzil",25,"Brown");
$p2= new person("Neshat",22,"Black");


$p1->details();
$p2->details();


  ?>
</body>
</html>