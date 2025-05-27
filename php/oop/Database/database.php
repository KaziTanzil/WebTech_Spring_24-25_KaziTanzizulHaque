<?php
 class database{

  private $db_host="localhost";
  private $db_user="root";
  private $db_pass="";
  private $db_name="testing";

  private $conn=false;
  private $result=array();
  private $mysqli="";

  function __construct()
  {
    if(!$this->conn)
    {
      $this->mysqli= new mysqli($this->db_host,$this->db_user,$this->db_pass,$this->db_name);
      $this->conn=true;


      if($this->mysqli->connect_error)
      {
        array_push($this->result,$this->mysqli->connect_error);
        return false;
      }
      else
      {
        return true;
      }
    }

  }
  

  function insert($table_name,$params=array()){
    if($this->table_exist($table_name))
    {
      print_r($params);

      $table_column=implode(" , ",array_keys($params));// Converts the keys of the $params array into a comma-separated string of column names
      $table_value = implode(" , ", array_map(function($value) {
    return "'$value'";
}, $params));// Converts the values of the $params array into a comma-separated string for insertion


      $sql="INSERT INTO $table_name ($table_column) VALUES ($table_value)";

      if($this->mysqli->query($sql))
      {
        array_push($this->result,$this->mysqli->insert_id);
        return true;
      }
      else{
        array_push($this->result,$this->mysqli->error);
        return false;
      }

    }
    else
    {
      return false;
    }
  }


   function update(){
    
  }

   function delete(){
    
  }


  private function table_exist($table)
  {
    $sql="SHOW TABLES FROM $this->db_name LIKE '$table'";
    $tableInDb= $this->mysqli->query($sql);
    if($tableInDb)
    {
      if($tableInDb->num_rows == 1)
      {
        return true;
      }
      else{
        array_push($this->result,$table."Does not exist the table in DB");
        return false;
      }
    }
  }

  public function getResult()
  {
    $val=$this->result;
    $this->result=array();
    return $val;
  }


    function __destruct()
  {
    if($this->conn)
    {
      if($this->mysqli->close())
      {
        $this->conn=false;
        return true;
      }

    }
    else{
      return false;
    }
    
  }



 }


?>