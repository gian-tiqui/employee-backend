<?php
require("../../db.php");
require("../../containers/response_container.php");
require("../../containers/control_origin.php");
header("Access-Control-Allow-Methods: POST");

$response = array();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = $_POST["email"];
  $pin = $_POST["pin"];

  $checkQuery = "SELECT * FROM users WHERE email LIKE ?";
  $checkStmt = $connection->prepare($checkQuery);

  if (!$checkStmt) {
    $response["status"] = 300;
    $response["description"] = "Database error: " . $connection->error;
    echo json_encode($response);
    exit();
  }

  $checkStmt->bind_param("s", $email);

  if ($checkStmt->execute()) {
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $response["status"] = 300;
        $response["description"] = "User already exists";
        echo json_encode($response);
        exit();
    }
  } else {
    $response["status"] = 300;
    $response["description"] = "Database error: " . $checkStmt->error;
    echo json_encode($response);
    exit();
  }

  $insertQuery = "INSERT INTO users (email, pin) VALUES (?, ?)";
  $insertStmt = $connection->prepare($insertQuery);

  if (!$insertStmt) {
    $response["status"] = 300;
    $response["description"] = "Database error: " . $connection->error;
    echo json_encode($response);
    exit();
  }

  $insertStmt->bind_param("ss", $email, $pin);

  if ($insertStmt->execute()) {
    $response["description"] = "User registered successfully";
    $response["status"] = 200;
  } else {
    $response["status"] = 300;
    $response["description"] = "Database error: " . $insertStmt->error;
  }

  $insertStmt->close();
  $checkStmt->close();
} else {
  $response["description"] = "Invalid Request Method";
  $response["status"] = 300;
}

$connection->close();

echo json_encode($response);
?>
