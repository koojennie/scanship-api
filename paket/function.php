<?php

error_reporting(0);

require '../inc/koneksi.php';

function error422($message) {
    $data = [
        'status' => 422,
        'message' => $message,
    ];
    header("HTTP/1.0 422 Unprocessable Entity");
    echo json_encode($data);
    exit();
}

function getLastResiOfDay($tanggal_pengiriman) {
    global $conn;
    
    $query = "SELECT no_resi FROM paket WHERE tanggal_pengiriman = '$tanggal_pengiriman' ORDER BY no_resi DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if($result) {
        $lastResi = mysqli_fetch_assoc($result);
        return $lastResi['no_resi'];
    } else {
        return null;
    }
}

function generateResi($tanggal_pengiriman) {
    $lastResi = getLastResiOfDay($tanggal_pengiriman);
    
    if ($lastResi) {
        $lastNumber = intval(substr($lastResi, -2));
        $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '01';
    }
    
    return 'SCS' . date('Ymd', strtotime($tanggal_pengiriman)) . $newNumber;
}

function storePaket($paketInput) {

    global $conn;

    $tanggal_pengiriman = mysqli_real_escape_string($conn, $paketInput['tanggal_pengiriman']);
    $no_resi = generateResi($tanggal_pengiriman);
    $nama_pengirim = mysqli_real_escape_string($conn, $paketInput['nama_pengirim']);
    $asal_pengirim = mysqli_real_escape_string($conn, $paketInput['asal_pengirim']);
    $nama_penerima = mysqli_real_escape_string($conn, $paketInput['nama_penerima']);
    $notelp_penerima = mysqli_real_escape_string($conn, $paketInput['notelp_penerima']);
    $alamat_tujuan = mysqli_real_escape_string($conn, $paketInput['alamat_tujuan']);
    $id_kurir = mysqli_real_escape_string($conn, $paketInput['id_kurir']);
    $status_tanggal = mysqli_real_escape_string($conn, $paketInput['status_tanggal']);
    $status_lokasi = mysqli_real_escape_string($conn, $paketInput['status_lokasi']);

    if(empty(trim($no_resi))) {
        return error422('Masukkan No Resi');
    }
    else if(empty(trim($tanggal_pengiriman))) {
        return error422('Masukkan Tanggal Pengiriman');
    }
    else if(empty(trim($nama_pengirim))) {
        return error422('Masukkan Nama Pengirim');
    }
    else if(empty(trim($asal_pengirim))) {
        return error422('Masukkan Asal Pengirim');
    }
    else if(empty(trim($nama_penerima))) {
        return error422('Masukkan Nama Penerima');
    }
    else if(empty(trim($notelp_penerima))) {
        return error422('Masukkan No Telp Penerima');
    }
    else if(empty(trim($alamat_tujuan))) {
        return error422('Masukkan Alamat Tujuan');
    }

    mysqli_begin_transaction($conn);

    try {

        // Insert into status
        $query1 = "INSERT INTO statuspaket (status_tanggal, status_lokasi, no_resi) VALUES ('$status_tanggal', '$status_lokasi', '$no_resi')";
        $result1 = mysqli_query($conn, $query1);

        if(!$result1) {
            throw new Exception("Error inserting into status");
        }

        // Insert into paket
        $query2 = "INSERT INTO paket (no_resi, tanggal_pengiriman, nama_pengirim, asal_pengirim, nama_penerima, notelp_penerima, alamat_tujuan, id_kurir) VALUES ('$no_resi', '$tanggal_pengiriman', '$nama_pengirim', '$asal_pengirim', '$nama_penerima', '$notelp_penerima', '$alamat_tujuan', '$id_kurir')";
        $result2 = mysqli_query($conn, $query2);

        if(!$result2) {
            throw new Exception("Error inserting into paket");
        }

        // Commit transaction
        mysqli_commit($conn);

        $data = [
            'status' => 201,
            'message' => 'Delivery Package and Status Created Successfully',
        ];
        header("HTTP/1.0 201 Created");
        return json_encode($data);

    } catch (Exception $e) {

        $data = [
            'status' => 500,
            'message' => 'Internal Server Error: ' . $e->getMessage(),
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);
    }

}

function getPaketList() {
    global $conn;

    $query = "SELECT paket.*, kurir.id_kurir, kurir.nama_kurir  FROM paket JOIN kurir ON paket.id_kurir = kurir.id_kurir";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        
        if(mysqli_num_rows($query_run) > 0) {

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Delivery Package List Fetched Succesfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            return json_encode($data);

        } else {
            $data = [
                'status' => 404,
                'message' => 'No Package Delivery Found',
            ];
            header("HTTP/1.0 404 No Package Delivery Found");
            return json_encode($data);
        }
    }
    else
    {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($data);

    }
}

function getPaket($paketParams) {
    global $conn;

    if (empty($paketParams['no_resi'])) {
        return error422('Masukkan No Resi Paket');
    }

    $paketresiId = mysqli_real_escape_string($conn, $paketParams['no_resi']);

    // Query untuk mendapatkan semua data dari paket dan statuspaket
    $query = "
        SELECT p.*, s.id_status, s.status_tanggal, s.status_lokasi, k.id_kurir, k.usn_kurir, k.nama_kurir, k.email_kurir, k.notelp_kurir
        FROM paket p
        INNER JOIN statuspaket s ON p.no_resi = s.no_resi
        LEFT JOIN kurir k ON p.id_kurir = k.id_kurir
        WHERE p.no_resi = '$paketresiId'
        ORDER BY s.status_tanggal DESC
    ";


    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $packageData = null;
            $statusData = [];

            while ($row = mysqli_fetch_assoc($result)) {
                if (!$packageData) {
                    // Ambil data paket hanya sekali
                    $packageData = [
                        'no_resi' => $row['no_resi'],
                        'tanggal_pengiriman' => $row['tanggal_pengiriman'],
                        'nama_pengirim' => $row['nama_pengirim'],
                        'asal_pengirim' => $row['asal_pengirim'],
                        'nama_penerima' => $row['nama_penerima'],
                        'notelp_penerima' => $row['notelp_penerima'],
                        'alamat_tujuan' => $row['alamat_tujuan'],
                        'id_kurir' => $row['id_kurir'],
                        'usn_kurir' => $row['usn_kurir'],
                        'nama_kurir' => $row['nama_kurir'],
                        'email_kurir' => $row['email_kurir'],
                        'notelp_kurir' => $row['notelp_kurir']
                    ];
                }
                // Tambahkan status ke array statusData
                $statusData[] = [
                    'id_status' => $row['id_status'],
                    'status_tanggal' => $row['status_tanggal'],
                    'status_lokasi' => $row['status_lokasi']
                ];
            }

            $response = [
                'status' => 200,
                'message' => 'Delivery Package Fetched Successfully',
                'data' => [
                    'package' => $packageData,
                    'status' => $statusData
                ]
            ];
            header("HTTP/1.0 200 OK");
            return json_encode($response);
        } else {
            $response = [
                'status' => 404,
                'message' => 'No Delivery Package Found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($response);
        }
    } else {
        $response = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header("HTTP/1.0 500 Internal Server Error");
        return json_encode($response);
    }
}


function updatePaket($paketInput, $paketParams) {

    global $conn;

    if(!isset($paketParams['no_resi'])) {
        return error422('No Resi tidak ditemukan pada URL');
    }
    else if($paketParams['no_resi'] == null) {
        return error422('Masukkan No Resi Paket');
    }

    $no_resi = mysqli_real_escape_string($conn, $paketParams['no_resi']);
    $tanggal_pengiriman = mysqli_real_escape_string($conn, $paketInput['tanggal_pengiriman']);
    $nama_pengirim = mysqli_real_escape_string($conn, $paketInput['nama_pengirim']);
    $asal_pengirim = mysqli_real_escape_string($conn, $paketInput['asal_pengirim']);
    $nama_penerima = mysqli_real_escape_string($conn, $paketInput['nama_penerima']);
    $notelp_penerima = mysqli_real_escape_string($conn, $paketInput['notelp_penerima']);
    $alamat_tujuan = mysqli_real_escape_string($conn, $paketInput['alamat_tujuan']);

    if(empty(trim($tanggal_pengiriman))) {
        return error422('Masukkan Tanggal Pengiriman');
    }
    else if(empty(trim($nama_pengirim))) {
        return error422('Masukkan Nama Pengirim');
    }
    else if(empty(trim($asal_pengirim))) {
        return error422('Masukkan Asal Pengirim');
    }
    else if(empty(trim($nama_penerima))) {
        return error422('Masukkan Nama Penerima');
    }
    else if(empty(trim($notelp_penerima))) {
        return error422('Masukkan No Telp Penerima');
    }
    else if(empty(trim($alamat_tujuan))) {
        return error422('Masukkan Alamat Tujuan');
    }

    else {
        $query = "UPDATE paket SET tanggal_pengiriman='$tanggal_pengiriman', nama_pengirim='$nama_pengirim', asal_pengirim='$asal_pengirim', nama_penerima='$nama_penerima', notelp_penerima='$notelp_penerima', alamat_tujuan='$alamat_tujuan' WHERE no_resi='$no_resi' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if($result) {
            $data = [
                'status' => 200,
                'message' => 'Delivery Package Updated Successfully',
            ];
            header("HTTP/1.0 200 Success");
            return json_encode($data);

        }
        else {
            $data = [
                'status' => 500,
                'message' => 'Internal Server Error',
            ];
            header("HTTP/1.0 500 Internal Server Error");
            return json_encode($data);
        }
    }

}

function deletePaket($paketParams) {
    global $conn;

    if(!isset($paketParams['no_resi'])) {
        return error422('No Resi tidak ditemukan pada URL');
    }
    else if($paketParams['no_resi'] == null) {
        return error422('Masukkan No Resi Paket');
    }

    $no_resi = mysqli_real_escape_string($conn, $paketParams['no_resi']);

    // hapus dari tabel status paket

    $queryStatusPaket = "DELETE FROM statuspaket WHERE no_resi='$no_resi'";
    $resultStatusPaket = mysqli_query($conn, $queryStatusPaket);


    // hapus dari paket
    $queryPaket = "DELETE FROM paket WHERE no_resi='$no_resi' LIMIT 1";
    $resultPaket = mysqli_query($conn, $queryPaket);





    if($resultPaket && mysqli_affected_rows($conn) > 0) {
        $data = [
            'status' => 200,
            'message' => 'Delivery Package Deleted Successfully',
        ];
        header("HTTP/1.0 200 Deleted");
        return json_encode($data);
    }
    else {
        $data = [
            'status' => 404,
            'message' => 'Delivery Package Not Found',
        ];
        header("HTTP/1.0 404 Not Found");
        return json_encode($data);
    }
}
?>