<?php
// get_command.php
header('Content-Type: application/json');
require 'config.php';

$device = isset($_GET['device']) ? $_GET['device'] : 'nodemcu';

// ambil command tertua yang belum served
$stmt = $pdo->prepare("SELECT * FROM commands WHERE device = ? AND served = 0 ORDER BY created_at ASC LIMIT 1");
$stmt->execute([$device]);
$cmd = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cmd) {
    echo json_encode(['command' => null]);
    exit;
}

// tandai served
$upd = $pdo->prepare("UPDATE commands SET served = 1 WHERE id = ?");
$upd->execute([$cmd['id']]);

echo json_encode([
    'command' => [
        'pump' => intval($cmd['pump']),
        'feed' => intval($cmd['feed']),
        'id' => intval($cmd['id']),
        'created_at' => $cmd['created_at']
    ]
]);
