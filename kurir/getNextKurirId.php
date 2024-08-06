<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');

include('function.php');

$nextId = getNextKurirId();

echo json_encode(['nextId' => $nextId]);
?>
