<?php
require("../../db.php");
require("../../containers/response_container.php");
require("../../containers/control_origin.php");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  $query = "SELECT * FROM employees";
  $stmt = $connection->prepare($query);

  if ($stmt->execute()) {
    $result = $stmt->get_result();
    $data = array();

    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }

    $response["description"] = "Employees Data";
    $response["data"] = $data;

    echo json_encode($response);
  } else {
    $response["status"] = 500;
    $response["description"] = "Error executing the query";

    echo json_encode($response);
  }
} else {
  $response["status"] = 500;
  $response["description"] = "Invalid Request Method";

  echo json_encode($response);
}
?>
