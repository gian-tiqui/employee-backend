<?php 
require("../../db.php");
require("../../containers/response_container.php");
require("../../containers/control_origin.php");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = $_POST["email"];
  $pin = $_POST["pin"];

  $query = "SELECT * FROM users WHERE email LIKE ? AND pin = ?";
  
  $stmt = $connection->prepare($query);

  if (!$stmt) {
    $response["status"] = 300;
    $response["description"] = "Database error: " . $connection->error;
    echo json_encode($response);
    exit();
  }

  $stmt->bind_param("si", $email, $pin);

  if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      $response["status"] = 300;
      $response["description"] = "No user found";
      
      echo json_encode($response);
      exit();
    }

    $user = $result->fetch_assoc();

    $response["description"] = "User found";
    $response["status"] = 200;
    $response["data"] = $user;
  } else {
    $response["status"] = 300;
    $response["description"] = "Database error: " . $stmt->error;
  }

  $stmt->close();
} else {
  $response["description"] = "Invalid Request Method";
  $response["status"] = 300;
}

$connection->close();

echo json_encode($response);
?>
