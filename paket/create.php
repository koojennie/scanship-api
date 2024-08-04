<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);


if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'){


    header('Access-Control-Allow-Origin:*');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Method: POST');
    header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

    include('function.php');

    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if($requestMethod == 'POST') {

        $inputData = json_decode(file_get_contents("php://input"), true);

        if(empty($inputData)) {
            $storePaket = storePaket($_POST);
        }
        else {
            $storePaket = storePaket($inputData);
        }

        echo $storePaket;
    }
    else
    {
        $data = [
            'status' => 405,
            'message' => $requestMethod. 'Method Not Allowed',
        ];
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode($data);
    }
} else {
    // Pengguna tidak memiliki akses ke halaman ini
    $data = [
        'status' => 403,
        'message' => 'Forbidden: You do not have access to this page',
    ];
    header("HTTP/1.0 403 Forbidden");
    echo json_encode($data);
    exit;
}
?>