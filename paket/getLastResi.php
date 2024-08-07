<?php
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

include('function.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['tanggal_pengiriman'])) {
    $tanggal_pengiriman = $_GET['tanggal_pengiriman'];
    $lastResi = getLastResiOfDay($tanggal_pengiriman);

    $data = [
        'status' => 200,
        'last_resi' => $lastResi,
    ];
    echo json_encode($data);
} else {
    $data = [
        'status' => 405,
        'message' => 'Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>
