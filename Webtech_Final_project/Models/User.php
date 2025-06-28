<?php
class User
{
  private $conn;

  public function __construct($dbConnection)
  {
    $this->conn = $dbConnection;
  }

  public function save($data)
  {
    // Store plain password as is (no hashing)
    $sql = "INSERT INTO user (ID, Name, Gender, PhoneNumber, Gmail, DoB, Password)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return ['success' => false, 'error' => $this->conn->error];
    }

    $stmt->bind_param(
      "sssssss",
      $data['userid'],
      $data['name'],
      $data['gender'],
      $data['phone'],
      $data['gmail'],
      $data['dob'],
      $data['password']  // Plain password here
    );

    if ($stmt->execute()) {
      return ['success' => true];
    } else {
      return ['success' => false, 'error' => $stmt->error];
    }
  }

  public function exists($field, $value)
  {
    $allowedFields = ['ID', 'Gmail', 'PhoneNumber'];

    if (!in_array($field, $allowedFields)) {
      throw new InvalidArgumentException("Invalid field for exists() check.");
    }

    $sql = "SELECT COUNT(*) FROM user WHERE $field = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }

    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return $count > 0;
  }

  public function getUserByGmail($gmail)
  {
    $sql = "SELECT * FROM user WHERE Gmail = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return null;

    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user ?: null;
  }
}
?>
