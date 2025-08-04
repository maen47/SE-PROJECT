<?php
include('db/db.php');

$lat  = $_GET['lat'];
$lng  = $_GET['lng'];
$dist = $_GET['dist'];

// ฟังก์ชันคำนวณระยะทาง (Haversine)
$sql = "SELECT id,name,phone,specialty,lat,lng,
        (6367 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) 
            + sin(radians(?)) * sin(radians(lat)))) AS distance 
        FROM technicians
        HAVING distance < ?
        ORDER BY distance ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("dddd",$lat,$lng,$lat,$dist);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode($data);
