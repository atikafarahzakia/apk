<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "config.php";

function getInput() {
    return json_decode(file_get_contents("php://input"), true);
}

$action = $_GET['action'] ?? '';

/* ============================================================
   🔹 REGISTER
   ============================================================ */
if ($action === 'register') {
    $data = getInput();
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');

    if (!$username || !$password) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check->execute([$username]);
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    echo json_encode(['success' => true, 'message' => 'Registrasi berhasil']);
    exit;
}

/* ============================================================
   🔹 LOGIN
   ============================================================ */
if ($action === 'login') {
    $data = getInput();
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Buat token unik (misal hash dari username + waktu)
        $token = base64_encode($username . '|' . time());
        echo json_encode(["success" => true, "token" => $token]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Username atau password salah"]);
    }
    exit;
}

/* ============================================================
   🔹 TOKEN VALIDASI (Dijalankan untuk semua CRUD)
   ============================================================ */
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "Token tidak ditemukan"]);
    exit;
}

$token = str_replace('Bearer ', '', $headers['Authorization']);
$decoded = base64_decode($token);
if (!$decoded || strpos($decoded, '|') === false) {
    http_response_code(403);
    echo json_encode(["error" => "Token tidak valid"]);
    exit;
}

/* ============================================================
   🔹 CRUD DATA
   ============================================================ */
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $conn->query("SELECT * FROM api ORDER BY id_api DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break; 

    case 'POST':
        $input = getInput();
        $stmt = $conn->prepare("INSERT INTO api (title, deskripsi) VALUES (?, ?)");
        $stmt->execute([$input['title'], $input['deskripsi']]);
        echo json_encode(["message" => "Data berhasil ditambahkan"]);
        break;

    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $query);
        $id = $query['id_api'] ?? 0;
        $input = getInput();
        $stmt = $conn->prepare("UPDATE api SET title = ?, deskripsi = ? WHERE id_api = ?");
        $stmt->execute([$input['title'], $input['deskripsi'], $id]);
        echo json_encode(["message" => "Data berhasil diupdate"]);
        break;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $query);
        $id = $query['id_api'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM api WHERE id_api = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Data berhasil dihapus"]);
        break;

    default:
        echo json_encode(["error" => "Metode tidak didukung"]);
}
