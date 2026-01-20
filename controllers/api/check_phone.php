<?php
require_once __DIR__ . '/../../models/bootstrap.php';
requireRoleJson('customer');

$in = readInput();
$phone = trim((string)($in['phone'] ?? ''));

if ($phone === '' || !isBdPhone($phone)) {
    jsonOut(['success' => true, 'exists' => false]);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, name, role FROM users WHERE phone = :phone LIMIT 1');
    $stmt->execute([':phone' => $phone]);
    $u = $stmt->fetch();

    if (!$u) {
        jsonOut(['success' => true, 'exists' => false]);
    }

    jsonOut([
        'success' => true,
        'exists' => true,
        'role' => $u['role'],
        'name' => $u['name'],
    ]);
} catch (Throwable $e) {
    safeServerError();
}
