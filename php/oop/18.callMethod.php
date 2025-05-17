<?php
 class student{


  private function abc()
  {
    echo "Hello";
  }

   private static function xyz()
  {
    echo "Hi";
  }

  function __call($method,$args)
  {
    echo "<br>You are not eligible to access <b> $method </b>.Because it is a private method";
  }

 }
 

  $obj1=new student();
  $obj1->abc();
  $obj1->xyz();
  
?>

