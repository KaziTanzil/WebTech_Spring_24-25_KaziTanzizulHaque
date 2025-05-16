<?php
  namespace classB{

  class test{
    function __construct()
    {
      echo"This is 'namespace classB'";
      echo"<br>";
      $A1=new \classA\test();
    }
  }
        function show()
  {
    echo"<br> This is <b>Namespace <b>classB</b> </b> show() function";
  }
  }

?>