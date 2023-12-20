<?php
require("../../db.php");
require("../../containers/response_container.php");
require("../../containers/file_location.php");
require("../../containers/control_origin.php");
header("Access-Control-Allow-Methods: POST");

function delete_json($JSON_LOCATION, $id) {
  $jsonContent = file_get_contents($JSON_LOCATION);
  $json = json_decode($jsonContent, true);

  foreach ($json["employees"] as $key => $employee) {
    if ($employee["eid"] == $id) {
      unset($json["employees"][$key]);
      break;
    }
  }

  $json["employees"] = array_values($json["employees"]);

  if (file_put_contents($JSON_LOCATION, json_encode($json, JSON_PRETTY_PRINT))) {
    return true;
  } else {
    return false;
  }
}

function delete_xml($XML_LOCATION, $id) {
  $xmlFile = simplexml_load_file($XML_LOCATION);

  if ($xmlFile === false) {
    return false;
  }

  $dEmployee = null;

  foreach ($xmlFile->employee as $employee) {
    if ((int)$employee['eid'] === (int)$id) {
      $dEmployee = $employee;
      break;
    }
  }

  if ($dEmployee) {
    $dom = dom_import_simplexml($dEmployee);
    $dom->parentNode->removeChild($dom);

    if ($xmlFile->asXML($XML_LOCATION)) {
      return true;
    } else {
      return false;
    }
  }

  return false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $eid = $_POST["eid"];

  $query = "DELETE FROM employees WHERE eid = ?";

  $stmt = $connection->prepare($query);

  $stmt->bind_param("i", $eid);

  if (!$stmt->execute()) {
    $response["status"] = 400;
    $response["description"] = "There was an error in deleting the data";

    echo json_encode($response);
    exit();
  }

  $stmt->close();

  if (!delete_json($JSON_LOCATION, $eid)) {
    $response["status"] = 300;
    $response["description"] = "There was a problem in deleting the employee in json";
    echo json_encode($response);
    exit();
  }

  if (!delete_xml($XML_LOCATION, $eid)) {
    $response["status"] = 300;
    $response["description"] = "There was a problem in deleting the employee in xml";
    echo json_encode($response);
    exit();
  }

  $response["status"] = 200;
  $response["description"] = "Employee Deleted Successfully";

  echo json_encode($response);
} else {
  $response["status"] = 500;
  $response["description"] = "Invalid Request Method";

  echo json_encode($response);
}

$connection->close();
?>
