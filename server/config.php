<?php
$host = "localhost";
$dbname = "db_ionic"; // ganti sesuai nama database kamu
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => "Koneksi gagal: " . $e->getMessage()]));
}
