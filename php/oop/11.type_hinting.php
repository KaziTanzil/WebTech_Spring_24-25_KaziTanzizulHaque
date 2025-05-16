<?php
 function sum(int $x)
 {
  echo $x+4;
 }

 sum(10.11);// result 14

 echo"<br>";
 sum(12);// result 16


 #sum("hello");          // error

 function y(array $y)
   {
    foreach($y as $x)
      echo"<br>".$x;
      
   }
   

   $fruit=["apple","banana","laura"];
   y($fruit);


?>


<?php
 class myname{
   function me()
   {
    echo"<br>Tanzil";
   }
 }
  class yourname{
  function you()
  {
    echo"<br>neshat";
  }
 }

 function name(myname $n)
 {
    $n->me();
 }


 $obj=new myname();

 name($obj);
  /*  $obj=new yourname();
                              //will show error cause name() only except myname class datatype
 name($obj);    
 
 */

?>