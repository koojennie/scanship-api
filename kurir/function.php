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

function storeKurir($kurirInput) {

    global $conn;

    $id_kurir = mysqli_real_escape_string($conn, $kurirInput['id_kurir']);
    $usn_kurir = mysqli_real_escape_string($conn, $kurirInput['usn_kurir']);
    $nama_kurir = mysqli_real_escape_string($conn, $kurirInput['nama_kurir']);
    $pw_kurir = mysqli_real_escape_string($conn, $kurirInput['pw_kurir']);
    $pw_kurir = password_hash($pw_kurir, PASSWORD_DEFAULT);
    $email_kurir = mysqli_real_escape_string($conn, $kurirInput['email_kurir']);
    $notelp_kurir = mysqli_real_escape_string($conn, $kurirInput['notelp_kurir']);

    if(empty(trim($id_kurir))) {
        return error422('Masukkan ID Kurir');
    }
    else if(empty(trim($usn_kurir))) {
        return error422('Masukkan Username Kurir');
    }
    else if(empty(trim($nama_kurir))) {
        return error422('Masukkan Nama Kurir');
    }
    else if(empty(trim($pw_kurir))) {
        return error422('Masukkan Password Kurir');
    }
    else if(empty(trim($email_kurir))) {
        return error422('Masukkan Email Kurir');
    }
    else if(empty(trim($notelp_kurir))) {
        return error422('Masukkan Nomor Telepon Kurir');
    }

    else {
        $query = "INSERT INTO kurir (id_kurir, usn_kurir, nama_kurir, pw_kurir, email_kurir, notelp_kurir) VALUES ('$id_kurir', '$usn_kurir', '$nama_kurir', '$pw_kurir', '$email_kurir', '$notelp_kurir')";
        $result = mysqli_query($conn, $query);

        if($result) {
            $data = [
                'status' => 201,
                'message' => 'Courier Inserted Successfully',
            ];
            header("HTTP/1.0 201 Inserted");
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

function getKurirList() {
    global $conn;

    $query = "SELECT * FROM kurir";
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        
        if(mysqli_num_rows($query_run) > 0) {

            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status' => 200,
                'message' => 'Courier List Fetched Succesfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            return json_encode($data);

        } else {
            $data = [
                'status' => 404,
                'message' => 'No Courier Found',
            ];
            header("HTTP/1.0 404 No Courier Found");
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

function getKurir($kurirParams) {
    global $conn;

    if($kurirParams['id_kurir'] == null) {
        return error422('Masukkan ID Kurir');
    }

    $kurirId = mysqli_real_escape_string($conn, $kurirParams['id_kurir']);

    $query = "SELECT * FROM kurir WHERE id_kurir='$kurirId' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if($result) {

        if(mysqli_num_rows($result) == 1) {
            $res = mysqli_fetch_assoc($result);

            $data = [
                'status' => 200,
                'message' => 'Courier Fetched Successfully',
                'data' => $res
            ];
            header("HTTP/1.0 200 Success");
            return json_encode($data);
        }
        else {
            $data = [
                'status' => 404,
                'message' => 'No Courier Found',
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

function updateKurir($kurirInput, $kurirParams) {

    global $conn;

    if(!isset($kurirParams['id_kurir'])) {
        return error422('ID Kurir tidak ditemukan pada URL');
    }
    else if($kurirParams['id_kurir'] == null) {
        return error422('Masukkan ID Kurir ScanShip');
    }

    $id_kurir = mysqli_real_escape_string($conn, $kurirInput['id_kurir']);
    $usn_kurir = mysqli_real_escape_string($conn, $kurirInput['usn_kurir']);
    $nama_kurir = mysqli_real_escape_string($conn, $kurirInput['nama_kurir']);
    $new_pw_kurir = mysqli_real_escape_string($conn, $kurirInput['new_pw_kurir']);
    $new_pw_kurir = password_hash($new_pw_kurir, PASSWORD_DEFAULT);
    $change_pw_kurir = isset($kurirInput['change_password']);
    $email_kurir = mysqli_real_escape_string($conn, $kurirInput['email_kurir']);
    $notelp_kurir = mysqli_real_escape_string($conn, $kurirInput['notelp_kurir']);

    if(empty(trim($usn_kurir))) {
        return error422('Masukkan Username Kurir');
    }
    else if(empty(trim($nama_kurir))) {
        return error422('Masukkan Nama Kurir');
    }
    else if(empty(trim($email_kurir))) {
        return error422('Masukkan Email Kurir');
    }
    else if(empty(trim($notelp_kurir))) {
        return error422('Masukkan Nomor Telepon Kurir');
    }

    if($change_pw_kurir && !empty($new_pw_kurir)) {
        $query = "UPDATE kurir SET usn_kurir='$usn_kurir', nama_kurir='$nama_kurir', pw_user='$new_pw_kurir', email_kurir='$email_kurir', notelp_kurir='$notelp_kurir' WHERE id_kurir='$id_kurir' LIMIT 1";
    }
    else {
        $query = "UPDATE kurir SET usn_kurir='$usn_kurir', nama_kurir='$nama_kurir', email_kurir='$email_kurir', notelp_kurir='$notelp_kurir' WHERE id_kurir='$id_kurir' LIMIT 1";
    }
        $result = mysqli_query($conn, $query);

    if($result) {
        $data = [
            'status' => 200,
            'message' => 'Courier Updated Successfully',
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

function deleteKurir($kurirParams) {
    global $conn;

    if(!isset($kurirParams['id_kurir'])) {
        return error422('ID Kurir tidak ditemukan pada URL');
    }
    else if($kurirParams['id_kurir'] == null) {
        return error422('Masukkan ID Kurir ScanShip');
    }

    $id_kurir = mysqli_real_escape_string($conn, $kurirParams['id_kurir']);

    $query = "DELETE FROM kurir WHERE id_kurir='$id_kurir' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if($result && mysqli_affected_rows($conn) > 0) {
        $data = [
            'status' => 200,
            'message' => 'Courier Deleted Successfully',
        ];
        header("HTTP/1.0 200 Deleted");
        return json_encode($data);
    }
    else {
        $data = [
            'status' => 404,
            'message' => 'Courier Not Found',
        ];
        header("HTTP/1.0 404 Not Found");
        return json_encode($data);
    }
}
?>
