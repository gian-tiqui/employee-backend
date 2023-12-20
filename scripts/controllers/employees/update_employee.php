<?php
require("../../db.php");
require("../../containers/response_container.php");
require("../../containers/file_location.php");
require("../../containers/control_origin.php");
header("Access-Control-Allow-Methods: POST");

function update_json($JSON_LOCATION, $data) {
  $jsonContent = file_get_contents($JSON_LOCATION);
  $json = json_decode($jsonContent, true);

  if ($json === null) {
    $response["status"] = 500;
    $response["description"] = "Error decoding JSON file";
    echo json_encode($response);
    exit();
  }

  foreach ($json["employees"] as &$employee) {
    if ($employee["eid"] == $data["eid"]) {
      $employee["first_name"] = $data["first_name"];
      $employee["middle_name"] = $data["middle_name"];
      $employee["last_name"] = $data["last_name"];
      $employee["address"] = $data["address"];
      $employee["contact_number"] = $data["contact_number"];
      $employee["email"] = $data["email"];
      break;
    }
  }

  if (file_put_contents($JSON_LOCATION, json_encode($json, JSON_PRETTY_PRINT))) {
    return true;
  }

  return false;
}

function update_xml($XML_LOCATION, $data) {
  $xmlFile = simplexml_load_file($XML_LOCATION);

  if ($xmlFile === false) {
    return false;
  }

  foreach ($xmlFile->employee as $employee) {
    if ((int)$employee['eid'] === (int)$data["eid"]) {
      $employee->first_name = $data["first_name"];
      $employee->middle_name = $data["middle_name"];
      $employee->last_name = $data["last_name"];
      $employee->address = $data["address"];
      $employee->contact_number = $data["contact_number"];
      $employee->email = $data["email"];

      if ($xmlFile->asXML($XML_LOCATION)) {
        return true;
      } else {
        return false;
      }
    }
  }

  return false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $data = array(
    "eid" => $_POST["eid"],
    "first_name" => $_POST["first_name"],
    "middle_name" => $_POST["middle_name"],
    "last_name" => $_POST["last_name"],
    "address" => $_POST["address"],
    "contact_number" => $_POST["contact_number"],
    "email" => $_POST["email"]
  );

  $updateQuery = "UPDATE employees SET first_name = ?, middle_name = ?, last_name = ?, address = ?, contact_number = ?, email = ? WHERE eid = ?";

  $stmt = $connection->prepare($updateQuery);

  $stmt->bind_param(
    "ssssssi",
    $data["first_name"],
    $data["middle_name"],
    $data["last_name"],
    $data["address"],
    $data["contact_number"],
    $data["email"],
    $data["eid"]
  );

  if (!$stmt->execute()) {
    $response["status"] = 400;
    $response["description"] = "Unable to update data";
    echo json_encode($response);
    exit();
  }

  $stmt->close();

  if (!update_json($JSON_LOCATION, $data)) {
    $response["status"] = 500;
    $response["description"] = "There was a problem in updating json";
    echo json_encode($response);
    exit();
  }

  if (!update_xml($XML_LOCATION, $data)) {
    $response["status"] = 500;
    $response["description"] = "There was a problem in updating xml";
    echo json_encode($response);
    exit();
  }

  $response["status"] = 200;
  $response["description"] = "Employee updated successfully";
  $response["data"] = $data;

  echo json_encode($response);
} else {
  $response["status"] = 500;
  $response["description"] = "Invalid Request Method";

  echo json_encode($response);
}

$connection->close();
?>
