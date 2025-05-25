<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testing";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM student";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} -  Name: {$row['student_name']} -  Age: {$row['age']} - City  {$row["city"]}<br>";
    }
} else {
    echo "No result found";
}

$conn->close();
?>
