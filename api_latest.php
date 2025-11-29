<?php
// api_latest.php
header('Content-Type: application/json');
require 'config.php';

$stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) { echo json_encode(null); exit; }
echo json_encode($r);
