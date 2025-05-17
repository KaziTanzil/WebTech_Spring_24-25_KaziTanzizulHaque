<?php
 class student{
   private $name="Tanzil";
  private $data=["name"=>"Tanzil","Age"=>25];
  public $id="22-47783-2";

  function __get($private_property)
  {
    echo "<br> You are not eligible to access <b> $private_property</b> Because it is a private or non existing property";
  }
 }

  $obj1=new student();
  echo $obj1->name;
  echo "<br>".$obj1->id;

  print_r ($obj1->data);
   echo $obj1->kjnsaHYUAXSIUJXAS;

?>