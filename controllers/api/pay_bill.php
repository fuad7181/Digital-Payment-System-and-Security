<?php
require_once __DIR__ . '/../../models/bootstrap.php';
requireRoleJson('customer');

$in = readInput();
$providerIdRaw = trim((string)($in['provider_id'] ?? ''));
$amountRaw = trim((string)($in['amount'] ?? ''));
$password = (string)($in['password'] ?? '');
$billNo = trim((string)($in['bill_no'] ?? $in['account'] ?? $in['reference'] ?? ''));

$errors = [];
$providerId = ctype_digit($providerIdRaw) ? (int)$providerIdRaw : 0;
if ($providerId <= 0) {
    $errors['provider_id'] = 'Please select a provider.';
}
$amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
if ($amount <= 0) {
    $errors['amount'] = 'Amount must be greater than 0.';
}
if ($password === '' || strlen($password) < 4) {
    $errors['password'] = 'Password must be at least 4 characters.';
}
if ($billNo !== '' && strlen($billNo) > 64) {
    $errors['bill_no'] = 'Bill/account number is too long.';
}

if ($errors) {
    jsonOut(['success' => false, 'errors' => $errors], 422);
}

try {
    $pdo = db();

    // Validate provider from bill_providers (not users)
    $stmt = $pdo->prepare("SELECT id, name, provider_code FROM bill_providers WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $providerId]);
    $provider = $stmt->fetch();

    if (!$provider) {
        jsonOut(['success' => false, 'errors' => ['provider_id' => 'Invalid provider.']], 422);
    }

    $pdo->beginTransaction();

    // Verify customer password (plain)
    $stmt = $pdo->prepare('SELECT id, balance, password FROM users WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => currentUserId()]);
    $cust = $stmt->fetch();
    if (!$cust) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if ((string)$password !== (string)($cust['password'] ?? '')) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['password' => 'Incorrect password.']], 422);
    }

    if ((float)$cust['balance'] < $amount) {
        $pdo->rollBack();
        jsonOut(['success' => false, 'errors' => ['amount' => 'Insufficient balance.']], 422);
    }

    // Deduct from customer
    $stmt = $pdo->prepare('UPDATE users SET balance = balance - :amt WHERE id = :id');
    $stmt->execute([':amt' => $amount, ':id' => currentUserId()]);

    // Record transaction (receiver_id NULL; provider fields filled)
    $stmt = $pdo->prepare('INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, bill_provider_id, provider_name, provider_code, reference, created_at)
                           VALUES (:type, :amount, :fee, :sid, NULL, :pid, :pname, :pcode, :ref, NOW())');
    $stmt->execute([
        ':type' => 'pay_bill',
        ':amount' => $amount,
        ':fee' => 0,
        ':sid' => currentUserId(),
        ':pid' => (int)$provider['id'],
        ':pname' => (string)$provider['name'],
        ':pcode' => (string)($provider['provider_code'] ?? ''),
        ':ref' => ($billNo !== '' ? $billNo : null),
    ]);

    $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = :id');
    $stmt->execute([':id' => currentUserId()]);
    $newBal = (float)($stmt->fetch()['balance'] ?? 0);

    $pdo->commit();
    jsonOut(['success' => true, 'message' => 'Bill paid successfully.', 'new_balance' => $newBal]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    safeServerError();
}
