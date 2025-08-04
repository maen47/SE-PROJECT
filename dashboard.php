<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">  <!-- สำคัญ -->
  <title>ค้นหาช่างใกล้ฉัน | ThunderFix</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css">
  <style>
      /* ให้แผนที่เต็มความกว้างและความสูงที่เหมาะสม */
      #map {
          width: 100%;
          height: 70vh; /* ปรับให้พอดีสำหรับมือถือ */
          min-height: 300px;
          border-radius: 10px;
          box-shadow: 0 0 15px rgba(0,0,0,0.3);
      }

      /* กำหนด margin-bottom สำหรับ container บนมือถือ */
      @media (max-width: 576px) {
          .container-fluid, .container {
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
  <div class="container-fluid">  <!-- container-fluid เพื่อเต็มจอมือถือ -->
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

<script>
let map, userMarker;

function initMap() {
  if (navigator.geolocation) {
     navigator.geolocation.getCurrentPosition((pos)=>{
        let myLatLng = {lat: pos.coords.latitude, lng: pos.coords.longitude};
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 14,
            center: myLatLng
        });

        userMarker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            title: "ตำแหน่งของคุณ"
        });

        loadTechnicians(myLatLng);
     }, ()=> {
        alert("ไม่สามารถระบุตำแหน่งของคุณได้");
     });
  } else {
     alert("Browser ไม่รองรับ GPS");
  }
}

function loadTechnicians(myLatLng){
    const dist = document.getElementById("distance").value;
    fetch(`load_tech.php?lat=${myLatLng.lat}&lng=${myLatLng.lng}&dist=${dist}`)
      .then(res=>res.json())
      .then(data=>{
          data.forEach(t=>{
            let marker = new google.maps.Marker({
                position:{lat:parseFloat(t.lat), lng:parseFloat(t.lng)},
                map:map,
            });

            let content = `<strong>${t.name}</strong><br>
                           ประเภท: ${t.specialty}<br>
                           เบอร์: ${t.phone}<br>
                           <a href="reviews.php?tid=${t.id}" class="btn btn-sm btn-primary mt-1">รีวิว</a>
                           <a href="create_or_get_conversation.php?technician_id=${t.id}" class="btn btn-sm btn-success mt-1 ms-1">แชทกับช่าง</a>`;

            let infoWindow = new google.maps.InfoWindow({ content: content });
            marker.addListener('click', function(){ infoWindow.open(map,marker); });
          });
      });
}

document.getElementById("distance").addEventListener("change", ()=>{
    navigator.geolocation.getCurrentPosition(p=>{
        let p2 = {lat:p.coords.latitude,lng:p.coords.longitude};
        loadTechnicians(p2);
    });
});
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY_HERE&callback=initMap" async defer></script>
</body>
</html>
