<?php
include("resources.php");
include("WateringUtil.php");
$data = json_decode(file_get_contents('php://input'), true);
if ($data['FUNC'] == "") {
    header("HTTP/1.1 400");
    echo "FUNC not set";
    exit;
} else{
    $todo = $data['FUNC'];
    switch($todo) {
        case "getAllCars":
            $cars = getAllCars($system);
            header("HTTP/1.1 200");
            echo json_encode($cars);        
            break;
        default:
            header("HTTP/1.1 500");
            echo "Invalid FUNC defined";       
    }
}



?>
