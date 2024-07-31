<?php

$host = "localhost:3307";
$username = "root";
$password = "";
$dbname = "scanship";

$conn = mysqli_connect($host, $username, $password, $dbname);


if(!$conn) {
    die("Connection Failed: ". mysli_connect_error());
}
?>