<!DOCTYPE html>
<html>
<body>

<?php

 
 
 class student{
     private $name;
     private $cgpa;
     
     public function __construct($name,$cgpa)
     {
      $this->name=$name;
      $this->cgpa=$cgpa;
     }
     
     public function setName($name)
     {
       $this->name=$name;
     }
      public function getName()
     {
       return $this->name;
     }
     public function setCgpa($cgpa)
     {
       $this->cgpa=$cgpa;
     }
      public function getCgpa()
     {
       return $this->cgpa;
     }
     
     public function showdetails()
     {
       echo "<br> Name = ". $this->getName();
       echo "<br>CGPA = ".$this->getCgpa();
       
     }
 }
 
 $stu=new student("mr.x",4.00);
 $stu->showdetails();
 


 
 

?> 

</body>
</html>