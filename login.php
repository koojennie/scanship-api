<?php

session_start();

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Method: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');

include('inc/koneksi.php');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == "POST") {
    // mendapatkan data yang dikirim dari body request
    $input = json_decode(file_get_contents("php://input"), true);
    
    // memeriksa apakah user input memiliki email dan password
    if(!isset($input['email']) || !isset($input['password'])) {
        $data = [
            'status' => 404,
            'message' => 'insert email and password',
        ];
        header("HTTP/1.0 400 Bad Request");
        echo json_encode($data);
        exit;
    }

    $username = mysqli_real_escape_string($conn, $input['email']);
    $password = mysqli_real_escape_string($conn, $input['password']);

    // search user in database 
    $query = "SELECT * FROM admin WHERE usn_admin =  '$username' ";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['pw_admin'])){
            // jika password benar sebagai admin
            $_SESSION['user_id'] = $user['id_admin'];
            $_SESSION['user_role'] = 'admin';

            $data = [
                'status' => 200,
                'message' => 'Login Succesfully as admin',
                'data' => [
                    'id'=>$user['id_admin'],
                    'name' => $user['usn_admin'],
                    'role' => 'admin'
                ]
            ];

            header("HTTP/1.0 200 OK");
            echo json_encode($data);
            exit;
        } else {
            // passsword salah
            $data = [
                'status' => 401,
                'message' => 'Password is wrong'
            ];
            header("HTTP/1.0 401 Unauthorized");
            echo json_encode($data);
            exit;
        }
    } else {
      // Mencari user di tabel kurir jika tidak ditemukan di admin
      $query = "SELECT * FROM kurir WHERE usn_kurir = '$username' LIMIT 1";
      $result = mysqli_query($conn, $query);

      if(mysqli_num_rows($result) > 0){
        $user =  mysqli_fetch_assoc($result);

        if(password_verify($password, $user['pw_kurir'])){
            // jika password benear, set sebagai kurir
            $_SESSION['user_id'] = $user['id_kurir'];

            $data = [
                'status' => 200,
                'message' => 'Login Successfully as a Kurir',
                'data' => [
                    'id' => $user['id_kurir'],
                    'email' => $user['email_kurir'],
                    'name' => $user['nama_kurir'],
                    'role' => 'kurir'
                ]
            ];
            header("HTTP/1.0 200 OK");
            echo json_encode($data);
            exit;
        } else {
            // Password salah
            $data = [
                'status' => 401,
                'message' => 'Password is wrong',
            ];
            header("HTTP/1.0 401 Unauthorized");
            echo json_encode($data);
            exit;
        }
      } else {
        // user tidak ditemukan
        $data = [
            'status' => 404,
            'message' => 'User not found',
        ];
        header("HTTP/1.0 404 Not Found");
        echo json_encode($data);
        exit;
      }
    }

}
else
{
    $data = [
        'status' => 405,
        'message' => $requestMethod. ' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>