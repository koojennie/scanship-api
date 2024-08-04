<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

include('../inc/koneksi.php');

function error422($message) {
    $data = [
        'status' => 422,
        'message' => $message,
    ];
    header("HTTP/1.0 422 Unprocessable Entity");
    echo json_encode($data);
    exit();
}


function postStatusWithScan($statusParam){
    global $conn;

    $statusTanggal = mysqli_real_escape_string($conn, $statusParam['status_tanggal']);
    $statusLokasi = mysqli_real_escape_string($conn, $statusParam['status_lokasi']);
    $noResi = mysqli_real_escape_string($conn, $statusParam['no_resi']);
    // $idKurir = mysqli_real_escape_string($conn, $statusParam['id_kurir']);
    


    if(empty($statusTanggal)) {
        return error422('Masukan status tanggal');
    }
    else if(empty($statusLokasi)) {
        return error422('Masukan status lokasi');
    }
    else if(empty($noResi)) {
        return error422("Masukan no resi paket");
    } 
    // else if(empty($idKurir)) {
    //     $idKurir = null;
    //     return $idKurir;
    // }
    else{ 
        try {
            $statusTanggalFormatted = DateTime::createFromFormat('Y-m-d\TH:i', $statusTanggal);
            $statusTanggalString = $statusTanggalFormatted->format('Y-m-d H:i:s');


            // insert into Statuspaket
            $query1 = "INSERT INTO statuspaket (status_tanggal, status_lokasi, no_resi) VALUES ('$statusTanggalString', '$statusLokasi', '$noResi')";
            $result1 = mysqli_query($conn, $query1);

            if(!$result1) {
                throw new Exception("Error inserting into statusPaket");
            }

            // // update
            // $query2 = "UPDATE paket SET id_kurir='$idKurir' WHERE no_resi = '$noResi'";
            // $result2 = mysqli_query($conn, $query2);

            // if(!$result2){
            //     throw new Exception("Error update into tabel paket");
            // }

            $data = [
                "status" => 201,
                'message'=> 'Status lokasi berhasil diupdate dan menambahkan no resi',
            ];
            header("HTTP/1.0 201 Created");
            return json_encode($data);

        } catch (Exception $e) {
            $data = [
                'status' => 500,
                'message' => 'Internal Server Error : '. $e->getMessage(),
            ];
            header("HTTP/1.0 500 Internal Server Error");
            return json_encode($data);
        }

    }
    
}


// if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'kurir'){

    // include('function.php');
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if($requestMethod == 'POST') {

        $inputData = json_decode(file_get_contents("php://input"), true);

        if(empty($inputData)) {
            $storePaket = postStatusWithScan($_POST);
        }
        else {
            $storePaket = postStatusWithScan($inputData);
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
// } else {
//     // Pengguna tidak memiliki akses ke halaman ini
//     $data = [
//         'status' => 403,
//         'message' => 'Forbidden: You do not have access to this page',
//     ];
//     header("HTTP/1.0 403 Forbidden");
//     echo json_encode($data);
//     exit;
// }
?>