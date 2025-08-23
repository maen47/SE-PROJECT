<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ค้นหาช่างใกล้ฉัน | ThunderFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            width: 100%;
            height: 70vh;
            min-height: 300px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 576px) {

            .container-fluid,
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }

            #distance {
                width: 100% !important;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ThunderFix</a>
            <div class="ms-auto">
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-3 mb-2">
        <label for="distance" class="form-label">ค้นหารัศมี (กม.)</label>
        <select id="distance" class="form-select w-auto">
            <option value="1" selected>1 km</option>
            <option value="5">5 km</option>
            <option value="10">10 km</option>
        </select>
    </div>

    <div class="container mb-4">
        <div id="map"></div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="js/map.js"></script>
</body>

</html>