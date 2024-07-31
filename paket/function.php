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

function storePaket($paketInput) {

    global $conn;

    $no_resi = mysqli_real_escape_string($conn, $paketInput['no_resi']);
    $tanggal_pengiriman = mysqli_real_escape_string($conn, $paketInput['tanggal_pengiriman']);
    $nama_pengirim = mysqli_real_escape_string($conn, $paketInput['nama_pengirim']);
    $asal_pengirim = mysqli_real_escape_string($conn, $paketInput['asal_pengirim']);
    $nama_penerima = mysqli_real_escape_string($conn, $paketInput['nama_penerima']);
    $notelp_penerima = mysqli_real_escape_string($conn, $paketInput['notelp_penerima']);
    $alamat_tujuan = mysqli_real_escape_string($conn, $paketInput['alamat_tujuan']);

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

    else {
        $query = "INSERT INTO paket (no_resi, tanggal_pengiriman, nama_pengirim, asal_pengirim, nama_penerima, notelp_penerima, alamat_tujuan) VALUES ('$no_resi', '$tanggal_pengiriman', '$nama_pengirim', '$asal_pengirim', '$nama_penerima', '$notelp_penerima', '$alamat_tujuan')";
        $result = mysqli_query($conn, $query);

        if($result) {
            $data = [
                'status' => 201,
                'message' => 'Delivery Package Created Successfully',
            ];
            header("HTTP/1.0 201 Created");
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

function getPaketList() {
    global $conn;

    $query = "SELECT * FROM paket";
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

    if($paketParams['no_resi'] == null) {
        return error422('Masukkan No Resi Paket');
    }

    $paketresiId = mysqli_real_escape_string($conn, $paketParams['no_resi']);

    $query = "SELECT * FROM paket WHERE no_resi='$paketresiId' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if($result) {

        if(mysqli_num_rows($result) == 1) {
            $res = mysqli_fetch_assoc($result);

            $data = [
                'status' => 200,
                'message' => 'Delivery Package Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            return json_encode($data);
        }
        else {
            $data = [
                'status' => 404,
                'message' => 'No Delivery Package Found',
            ];
            header("HTTP/1.0 404 Not Found");
            return json_encode($data);
        }
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

    $query = "DELETE FROM paket WHERE no_resi='$no_resi' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if($result && mysqli_affected_rows($conn) > 0) {
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