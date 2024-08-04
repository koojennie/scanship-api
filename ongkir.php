<?php
header('Content-Type: application/json');

// Harga per kilogram untuk setiap layanan
$hargaPerKg = [
    "Lightning" => 45000,
    "Swift" => 30000,
    "Tomorrow" => 20000,
    "Horizon" => 13000,
    "Leisure" => 8000
];

// Estimasi tiba untuk setiap layanan
$estimasiTiba = [
    "Lightning" => "Jam",
    "Swift" => "0 hari",
    "Tomorrow" => "1 hari",
    "Horizon" => "2 hari",
    "Leisure" => "3 hari"
];

// Jenis kiriman
$jenisKiriman = "Paket";

// Mendapatkan input berat dari request
$berat = isset($_GET['berat']) ? floatval($_GET['berat']) : 0;

// Menyiapkan array hasil
$hasil = [];

if ($berat > 0) {
    foreach ($hargaPerKg as $layanan => $harga) {
        $ongkir = $harga * $berat;
        $hasil[] = [
            "layanan" => $layanan,
            "jenis_kiriman" => $jenisKiriman,
            "estimasi_tiba" => $estimasiTiba[$layanan],
            "ongkir" => "Rp " . number_format($ongkir, 0, ',', '.')
        ];
    }
}

// Mengirimkan hasil dalam format JSON
echo json_encode($hasil);
?>
