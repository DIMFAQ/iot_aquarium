<?php
// api_history.php
header('Content-Type: application/json');
require 'config.php';

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
if ($limit < 1) $limit = 50;
if ($limit > 1000) $limit = 1000;

$stmt = $pdo->prepare("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->execute();
$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($arr);
