<?php
// control.php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$device = $_POST['device'] ?? 'nodemcu';
$feed = $_POST['feed'] ?? null;

// Validasi feed: hanya boleh 'start' atau 'stop'
if ($feed === 'start') {
    $feed_val = 1;
} elseif ($feed === 'stop') {
    $feed_val = 0;
} else {
    http_response_code(400);
    echo "Invalid feed command";
    exit;
}

// Simpan perintah ke tabel commands, hanya kolom feed
$stmt = $pdo->prepare("INSERT INTO commands (device, feed) VALUES (?, ?)");
$stmt->execute([$device, $feed_val]);

// Jika AJAX, respon JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['status'=>'ok']);
    exit;
}

header('Location: index.php?sent=1');
exit;
