<?php
require 'first.php';    // Includes file containing classA namespace
require 'second.php';   // Includes file containing classB namespace

function show() {
  echo "<br> This is <b> Home show() </b> function";
}

$obj = new classA\test();  // Creates object of classA\test
echo "<br>";
$obj1 = new classB\test(); // Creates object of classB\test

show();                    // Calls the local show() function

classA\show();

classB\show();
?>