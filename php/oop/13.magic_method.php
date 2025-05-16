<?php

class MagicDemo {
    private $data = [];

    // Called when the object is created
    public function __construct() {
        echo "Constructor: Object Created!<br>";
    }

    // Called when the object is destroyed
    public function __destruct() {
        echo "Destructor: Object Destroyed!<br>";
    }

    // Called when trying to get an undefined or private property
    public function __get($name) {
        echo "Getting '$name'<br>";
        return isset($this->data[$name]) ? $this->data[$name] : "Not Found";
    }

    // Called when trying to set an undefined or private property
    public function __set($name, $value) {
        echo "Setting '$name' to '$value'<br>";
        $this->data[$name] = $value;
    }

    // Called when we check if a property is set
    public function __isset($name) {
        echo "Is '$name' set?<br>";
        return isset($this->data[$name]);
    }

    // Called when we unset a property
    public function __unset($name) {
        echo "Unsetting '$name'<br>";
        unset($this->data[$name]);
    }

    // Called when we use echo on the object
    public function __toString() {
        return "Object as String<br>";
    }

    // Called when object is used as a function
    public function __invoke($arg) {
        echo "Object called like a function with arg: $arg<br>";
    }
}

// ========= Demo =========
$obj = new MagicDemo();

// __set and __get
$obj->name = "Tanziz";
echo $obj->name . "<br>";

// __isset and __unset
isset($obj->name);
unset($obj->name);
echo $obj->name . "<br>";  // Try to get after unset

// __toString
echo $obj;

// __invoke
$obj("Hello from invoke!");

?>
