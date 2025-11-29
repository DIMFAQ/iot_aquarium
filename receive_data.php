<?php
// receive_data.php
header('Content-Type: application/json');
require 'config.php';

// Accept POST x-www-form-urlencoded or raw JSON
$data = $_POST ?: json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['device'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad request']);
    exit;
}

$device = $data['device'];
$temp = isset($data['temp_c']) ? floatval($data['temp_c']) : null;
$turb = isset($data['turbidity_raw']) ? intval($data['turbidity_raw']) : null;
$dist = isset($data['distance_cm']) ? floatval($data['distance_cm']) : null;
$pump = isset($data['pump_state']) ? intval($data['pump_state']) : 0;
$feed = isset($data['feed_event']) ? intval($data['feed_event']) : 0;

$stmt = $pdo->prepare("INSERT INTO sensor_data (device, temp_c, turbidity_raw, distance_cm, pump_state, feed_event) VALUES (?,?,?,?,?,?)");
$stmt->execute([$device, $temp, $turb, $dist, $pump, $feed]);

echo json_encode(['status' => 'ok']);
