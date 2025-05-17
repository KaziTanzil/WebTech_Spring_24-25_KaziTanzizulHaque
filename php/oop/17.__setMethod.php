<?php
 class student{
   private $name;
 

  function __set($private_property, $value)
  {
    echo "<br>You are not eligible to set a value of <b> $private_property = $value </b>.Because it is a private or non existing property";
  }
 }

  $obj1=new student();
  $obj1->name=10;
  $obj1->hkjasuih=50;
  
?>