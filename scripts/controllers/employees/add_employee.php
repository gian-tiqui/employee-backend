<?php
require("../../db.php");
require("../../containers/response_container.php");
require("../../containers/file_location.php");
require("../../containers/control_origin.php");
header("Access-Control-Allow-Methods: POST");

function add_json($JSON_LOCATION, $data, $id) {
  $jsonContent = file_get_contents($JSON_LOCATION);
  $json = json_decode($jsonContent, true);

  if ($json === null) {
      $response["status"] = 500;
      $response["description"] = "Error decoding JSON file";
      echo json_encode($response);
      exit();
  }

  $employee = [
      "eid" => $id,
      "first_name" => $data["first_name"],
      "middle_name" => $data["middle_name"],
      "last_name" => $data["last_name"],
      "address" => $data["address"],
      "contact_number" => $data["contact_number"],
      "email" => $data["email"]
  ];

  $json['employees'][] = $employee;

  if (file_put_contents($JSON_LOCATION, json_encode($json, JSON_PRETTY_PRINT))) {
    return true;
  }

  return false;
}

function add_xml($XML_LOCATION, $data, $id) {
  $xmlFile = simplexml_load_file($XML_LOCATION);

  $employee = $xmlFile->addChild("employee");
  $employee->addAttribute("eid", $id);
  $employee->addChild("first_name", $data["first_name"]);
  $employee->addChild("middle_name", $data["middle_name"]);
  $employee->addChild("last_name", $data["last_name"]);
  $employee->addChild("address", $data["address"]);
  $employee->addChild("contact_number", $data["contact_number"]);
  $employee->addChild("email", $data["email"]);

  if ($xmlFile->asXML($XML_LOCATION)) {
    return true;
  }

  return false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $data = array(
    "first_name" => $_POST["first_name"],
    "middle_name" => $_POST["middle_name"],
    "last_name" => $_POST["last_name"],
    "address" => $_POST["address"],
    "contact_number" => $_POST["contact_number"],
    "email" => $_POST["email"]
  );

  $check_query = "SELECT COUNT(*) FROM employees WHERE email = ?";
  $check_stmt = $connection->prepare($check_query);
  $check_stmt->bind_param("s", $data["email"]);
  $check_stmt->execute();
  $check_stmt->bind_result($email_count);
  $check_stmt->fetch();
  $check_stmt->close();

  if ($email_count > 0) {
    $response["status"] = 400;
    $response["description"] = "Email already exists";
    echo json_encode($response);
    exit();
  }

  $query = "INSERT INTO employees (first_name, middle_name, last_name, address, contact_number, email) VALUES (?, ?, ?, ?, ?, ?)";
  
  $stmt = $connection->prepare($query);

  $stmt->bind_param(
    "ssssss",
    $data["first_name"],
    $data["middle_name"],
    $data["last_name"],
    $data["address"],
    $data["contact_number"],
    $data["email"]
  );

  if (!$stmt->execute()) {
    $response["status"] = 400;
    $response["description"] = "Unable to insert data";
    echo json_encode($response);
    exit();
  }

  $stmt->close();

  $id_query = "SELECT MAX(eid) AS id FROM employees";
  $id_result = $connection->query($id_query);
  $id_row = $id_result->fetch_assoc();
  $id = $id_row["id"];

  if (!add_json($JSON_LOCATION, $data, $id)) {
    $response["status"] = 400;
    $response["description"] = "Unable to insert data to json";
    echo json_encode($response);
    exit();
  }
  
  if (!add_xml($XML_LOCATION, $data, $id)) {
    $response["status"] = 400;
    $response["description"] = "Unable to insert data to xml";
    echo json_encode($response);
    exit();
  }

  $response["status"] = 200;
  $response["description"] = "Data added successfully";
  $response["data"] = $data;

  echo json_encode($response);
} else {
  $response["status"] = 500;
  $response["description"] = "Invalid Request Method";
  echo json_encode($response);
}

$connection->close();
?>
