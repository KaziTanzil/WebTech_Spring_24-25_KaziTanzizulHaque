<?php
  //require "abc.php";
  //require "mno.php";
  //require "xyz.php";
 
spl_autoload_register(function ($className) {
    require $className . ".php";
});


  $obj1=new abc();
  $obj2=new xyz();
  $obj3=new mno();
?>