<?php
 class myself{
  function A()
  {
    echo "<br>My name is Tanzil";
    return $this;
  }
  function B()
  {
    echo "<br>I am  25 years old";
    return $this;
  }
  function C()
  {
    echo"<br>I am a student of AIUB";
    return $this;
  }

 }

 $obj = new myself();
 $obj->A()->B()->C();

?>