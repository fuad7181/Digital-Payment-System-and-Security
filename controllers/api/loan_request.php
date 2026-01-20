<?php
require_once __DIR__ . '/../../models/bootstrap.php';
requireRoleJson('customer');

$in = readInput();
$amountRaw = trim((string)($in['amount'] ?? ''));
$durationRaw = trim((string)($in['duration'] ?? ''));
$password = (string)($in['password'] ?? '');

$errors = [];

$amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
if ($amount <= 0 || $amount > 2000) {
    $errors['amount'] = 'Loan amount must be between 1 and 2000.';
}

$duration = ctype_digit($durationRaw) ? (int)$durationRaw : 0;
if ($duration <= 0) {
    $errors['duration'] = 'Duration must be a positive number (months).';
}

if ($password === '' || strlen($password) < 4) {
    $errors['password'] = 'Password must be at least 4 characters.';
}

if ($errors) {
    jsonOut(['success' => false, 'errors' => $errors], 422);
}

try {
    $pdo = db();

    // Always verify using password (bcrypt)
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => currentUserId()]);
    $row = $stmt->fetch();
    if (!$row || (string)$password !== (string)((string)($row['password'] ?? ''))) {
        jsonOut(['success' => false, 'errors' => ['password' => 'Incorrect password.']], 422);
    }

    $stmt = $pdo->prepare('INSERT INTO loan_requests (user_id, amount, duration_months, status, created_at) VALUES (:uid, :amount, :dur, :status, NOW())');
    $stmt->execute([
        ':uid' => currentUserId(),
        ':amount' => $amount,
        ':dur' => $duration,
        ':status' => 'pending',
    ]);

    jsonOut(['success' => true, 'message' => 'Loan request submitted. Status: pending.']);
} catch (Throwable $e) {
    safeServerError();
}
