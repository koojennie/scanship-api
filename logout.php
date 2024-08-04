<?php
session_start(); // Memulai sesi
session_destroy(); // Menghapus semua data sesi
header('Content-Type: application/json');
echo json_encode(['status' => 200, 'message' => 'Logged out successfully']);
?>
