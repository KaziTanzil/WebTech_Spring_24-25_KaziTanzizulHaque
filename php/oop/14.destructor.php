<?php
 class magicmethod{
  function __construct()
  {
    echo"<br> This is the constructor";
  }

  function hello()
  {
    echo"<br> This is a function";
  }

   function __destruct()
  {
    echo"<br> This is the desstructor";
  }
 }

  $obj=new magicmethod();
  $obj->hello();
  

?>