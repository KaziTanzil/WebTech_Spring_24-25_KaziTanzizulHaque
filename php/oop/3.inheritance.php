<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inheritamce</title>
</head>
<body>
   <?php
    class Address {
    public string $houseNo;
    public string $roadNo;
    public string $district;
    public string $divison;
    
    public function __construct($houseNo, $roadNo, $district, $divison) {
        $this->houseNo = $houseNo;
        $this->roadNo = $roadNo;
        $this->district = $district;
        $this->division = $divison;
    }

   public function fullAddress()
   {
    return "House No. ".$this->houseNo." Road No. ".$this->roadNo." District= ".$this->district." Division= ".$this->division;
   }
}

    class person{
        private $name;
        private $age;
        private $address;
        
        public function __construct($name,$age,$address)
        {
          $this->name=$name;
          $this->age=$age;
          $this->address=$address;
        }

        public function setName($name)
        {
          $this->name=$name;
        }

        public function getName()
        {
          return $this->name;
        }
        
        public function setAge($age)
        {
          $this->age=$age;
        }

        public function getAge()
        {
          return $this->age;
        }
        
        public function setAddress($address)
        {
          $this->address=$address;
        }

        public function getAddress()
        {
          return $this->address->fullAddress();
        }


        public function info()
        {
          echo "<h1> Info of a person </h1><br>";
          echo"Name: ".$this->getName()."<br>";
          echo"Age: ".$this->getAge()."<br>";
          echo"Address: ".$this->getAddress()."<br>";
        }
    }

    class student extends person{
      private $university;
      private $ID;
      private $CGPA;

      public function __construct($name,$age,$Address,$university,$ID,$CGPA)
       {
        parent::__construct($name,$age,$Address);
        $this->university=$university;
        $this->ID=$ID;
        $this->CGPA=$CGPA;
        
       }
     
        public function setUniversity($university)
        {
          $this->university=$university;
        }

        public function getUniversity()
        {
          return $this->university;
        }
         public function setId($ID)
        {
          $this->ID=$ID;
        }

        public function getId()
        {
          return $this->ID;
        }
         public function setCgpa($CGPA)
        {
          $this->CGPA=$CGPA;
        }

        public function getCgpa()
        {
          return $this->CGPA;
        }

        

          public function info()
        {
          echo "<h1> Info of a student </h1><br>";
          echo"Name: ".$this->getName()."<br>";
          echo"Age: ".$this->getAge()."<br>";
          echo"Address: ".$this->getAddress()."<br>";
          echo"University Name: ".$this->getUniversity()."<br>";
          echo"ID: ".$this->getId()."<br>";
          echo"CGPA: ".$this->getCgpa()."<br>";
        }
    }

    $a1=new Address(8,8,"Dhaka","Dhaka");
    $p1=new person("Tanzil",25,$a1);
    $p1->info();
    $s1=new student("Tanzil",25,$a1,"AIUB","22-47783-2",3.95);
    $s1->info();
   ?>
</body>
</html>