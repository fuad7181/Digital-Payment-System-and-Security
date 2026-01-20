<?php
require_once __DIR__ . '/../models/bootstrap.php';

function custIsAjax(): bool {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

function custJson(array $data): void {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getCurrentCustomer(): array {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, name, email, phone, profile_image, balance FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => currentUserId()]);
    $row = $stmt->fetch();
    return $row ?: ['id' => 0, 'name' => 'Unknown', 'email' => '', 'phone' => '', 'profile_image' => null, 'balance' => 0.0];
}

function getProviders(): array {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name, provider_code FROM bill_providers ORDER BY name ASC");
    $rows = $stmt->fetchAll();
    return is_array($rows) ? $rows : [];
}

function dashboard(): void {
    requireCustomerRedirect();
    $user = getCurrentCustomer();
    $balance = (float)$user['balance'];
    $success = flashGet('success', '');
    $errors  = flashGet('errors', []);
    require __DIR__ . '/../views/customer/dashboard.php';
}

/**
 * Profile view
 */
function profile(): void {
    requireCustomerRedirect();

    $user = getCurrentCustomer();
    $balance = (float)$user['balance'];

    $success = flashGet('success', '');
    $errors  = flashGet('errors', []);
    $fieldErrors = flashGet('field_errors', []);

    require __DIR__ . '/../views/customer/profile.php';
}

/**
 * Profile update (POST)
 */
function updateProfile(): void {
    requireCustomerRedirect();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=Customer/profile');
        exit;
    }

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $errors = [];
    $fieldErrors = [];

    if ($name === '' || strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
        $fieldErrors['name'] = 'Enter a valid name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
        $fieldErrors['email'] = 'Enter a valid email.';
    }

    if (!isBdPhone($phone)) {
        $errors[] = 'Phone must be a valid BD number (01XXXXXXXXX).';
        $fieldErrors['phone'] = 'Use format 01XXXXXXXXX.';
    }

    // Optional: profile image upload
    $newProfileImagePath = null;
    if (!$errors && isset($_FILES['profile_image']) && is_array($_FILES['profile_image'])) {
        $f = $_FILES['profile_image'];
        if (!empty($f['name']) && (int)($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ((int)$f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Profile image upload failed.';
                $fieldErrors['profile_image'] = 'Upload failed.';
            } else {
                // Basic validations
                $maxBytes = 2 * 1024 * 1024; // 2MB
                if ((int)$f['size'] > $maxBytes) {
                    $errors[] = 'Profile image must be 2MB or less.';
                    $fieldErrors['profile_image'] = 'Max size 2MB.';
                } else {
                    $tmp = (string)$f['tmp_name'];
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tmp) ?: '';
                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                        'image/webp' => 'webp',
                    ];
                    if (!isset($allowed[$mime])) {
                        $errors[] = 'Profile image must be JPG, PNG, GIF, or WEBP.';
                        $fieldErrors['profile_image'] = 'Invalid file type.';
                    } else {
                        $ext = $allowed[$mime];
                        $uploadDir = __DIR__ . '/../public/uploads/profile';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0775, true);
                        }
                        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                            $errors[] = 'Server cannot save the uploaded image.';
                            $fieldErrors['profile_image'] = 'Try again later.';
                        } else {
                            $filename = 'u' . currentUserId() . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                            $destAbs = $uploadDir . '/' . $filename;
                            if (!move_uploaded_file($tmp, $destAbs)) {
                                $errors[] = 'Could not save the uploaded image.';
                                $fieldErrors['profile_image'] = 'Upload failed.';
                            } else {
                                // Store as web path relative to public/
                                $newProfileImagePath = 'uploads/profile/' . $filename;
                            }
                        }
                    }
                }
            }
        }
    }

    if (!$errors) {
        try {
            $pdo = db();

            // prevent duplicate email/phone for other users
            $stmt = $pdo->prepare('
                SELECT id FROM users
                WHERE (email = :email OR phone = :phone) AND id <> :id
                LIMIT 1
            ');
            $stmt->execute([
                ':email' => $email,
                ':phone' => $phone,
                ':id'    => currentUserId()
            ]);

            if ($stmt->fetch()) {
                $errors[] = 'Email or phone already exists.';
            } else {
                // If a new profile image was uploaded, also update it
                if ($newProfileImagePath !== null) {
                    // Remove old image file (best effort)
                    $stmtOld = $pdo->prepare('SELECT profile_image FROM users WHERE id = :id LIMIT 1');
                    $stmtOld->execute([':id' => currentUserId()]);
                    $old = $stmtOld->fetch();
                    $oldPath = trim((string)($old['profile_image'] ?? ''));
                    if ($oldPath !== '' && strpos($oldPath, 'uploads/profile/') === 0) {
                        $oldAbs = __DIR__ . '/../public/' . $oldPath;
                        if (is_file($oldAbs)) {
                            @unlink($oldAbs);
                        }
                    }

                    $stmt = $pdo->prepare('
                        UPDATE users
                        SET name = :name, email = :email, phone = :phone, profile_image = :img
                        WHERE id = :id
                    ');
                    $stmt->execute([
                        ':name'  => $name,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':img'   => $newProfileImagePath,
                        ':id'    => currentUserId()
                    ]);
                } else {
                    $stmt = $pdo->prepare('
                        UPDATE users
                        SET name = :name, email = :email, phone = :phone
                        WHERE id = :id
                    ');
                    $stmt->execute([
                        ':name'  => $name,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':id'    => currentUserId()
                    ]);
                }

                $_SESSION['success'] = 'Profile updated successfully.';
                header('Location: index.php?url=Customer/profile');
                exit;
            }
        } catch (Throwable $e) {
            $errors[] = 'Server error. Please try again.';
        }
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['field_errors'] = $fieldErrors;
    header('Location: index.php?url=Customer/profile');
    exit;
}

/**
 * Backward compatible route used by edit_profile.php (if present)
 */
function editProfile(): void {
    updateProfile();
}

function send(): void {
    requireCustomerRedirect();

    $user = getCurrentCustomer();
    $balance = (float)$user['balance'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $receiverPhone = trim($_POST['receiver'] ?? '');
        $amountRaw     = trim($_POST['amount'] ?? '');
        $password      = trim((string)($_POST['password'] ?? ''));

        $errors = [];
        $fieldErrors = [];

        if (!isBdPhone($receiverPhone)) {
            $errors[] = 'Receiver must be a valid BD number (01XXXXXXXXX).';
            $fieldErrors['receiver'] = 'Use format 01XXXXXXXXX.';
        }

        $amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
        if ($amount <= 0) {
            $errors[] = 'Amount must be a number greater than 0.';
            $fieldErrors['amount'] = 'Enter a valid amount (> 0).';
        }

        if ($password === '' || strlen($password) < 4) {
            $errors[] = 'Password must be at least 4 characters.';
            $fieldErrors['password'] = 'Password must be at least 4 characters.';
        }

        if (!$errors) {
            try {
                $pdo = db();

                // Receiver must exist and be a customer
                $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = :phone AND role = "customer" LIMIT 1');
                $stmt->execute([':phone' => $receiverPhone]);
                $receiver = $stmt->fetch();

                if (!$receiver) {
                    $errors[] = 'Receiver not found (customer only).';
                    $fieldErrors['receiver'] = 'Receiver not found.';
                } elseif ((int)$receiver['id'] === currentUserId()) {
                    $errors[] = 'You cannot send money to yourself.';
                    $fieldErrors['receiver'] = 'Cannot send to yourself.';
                } else {
                    $pdo->beginTransaction();

                    // lock sender
                    $stmt = $pdo->prepare('SELECT balance, password FROM users WHERE id = :id FOR UPDATE');
                    $stmt->execute([':id' => currentUserId()]);
                    $sender = $stmt->fetch();

                    if (!$sender || ($password !== (string)$sender['password'])) {
                        $pdo->rollBack();
                        $errors[] = 'Incorrect password.';
                        $fieldErrors['password'] = 'Incorrect password.';
                    } elseif ((float)$sender['balance'] < $amount) {
                        $pdo->rollBack();
                        $errors[] = 'Insufficient balance.';
                        $fieldErrors['amount'] = 'Insufficient balance.';
                    } else {
                        // lock receiver
                        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id FOR UPDATE');
                        $stmt->execute([':id' => (int)$receiver['id']]);
                        if (!$stmt->fetch()) {
                            $pdo->rollBack();
                            $errors[] = 'Receiver not found.';
                            $fieldErrors['receiver'] = 'Receiver not found.';
                        } else {
                            $stmt = $pdo->prepare('UPDATE users SET balance = balance - :amt WHERE id = :id');
                            $stmt->execute([':amt' => $amount, ':id' => currentUserId()]);

                            $stmt = $pdo->prepare('UPDATE users SET balance = balance + :amt WHERE id = :id');
                            $stmt->execute([':amt' => $amount, ':id' => (int)$receiver['id']]);

                            $stmt = $pdo->prepare('
                                INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, reference, created_at)
                                VALUES (:type, :amount, :fee, :sid, :rid, :ref, NOW())
                            ');
                            $stmt->execute([
                                ':type'   => 'send_money',
                                ':amount' => $amount,
                                ':fee'    => 0,
                                ':sid'    => currentUserId(),
                                ':rid'    => (int)$receiver['id'],
                                ':ref'    => $receiverPhone,
                            ]);

                            $pdo->commit();

                            $_SESSION['success'] = 'Money sent successfully.';

                            if (custIsAjax()) {
                                custJson([
                                    'status'   => 'success',
                                    'message'  => $_SESSION['success'],
                                    'redirect' => 'index.php?url=Customer/dashboard'
                                ]);
                            }

                            header('Location: index.php?url=Customer/dashboard');
                            exit;
                        }
                    }
                }
            } catch (Throwable $e) {
                if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Server error. Please try again.';
            }
        }

        if ($errors) {
            if (custIsAjax()) {
                custJson([
                    'status'       => 'error',
                    'errors'       => $errors,
                    'field_errors' => $fieldErrors
                ]);
            }
            $_SESSION['errors'] = $errors;
            header('Location: index.php?url=Customer/send');
            exit;
        }
    }

    $success = flashGet('success', '');
    $errors  = flashGet('errors', []);
    require __DIR__ . '/../views/customer/send.php';
}

function cashout(): void {
    requireCustomerRedirect();

    $user = getCurrentCustomer();
    $balance = (float)$user['balance'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $agentPhone = trim($_POST['agent'] ?? '');
        $amountRaw  = trim($_POST['amount'] ?? '');
        $password   = trim((string)($_POST['password'] ?? ''));

        $errors = [];
        $fieldErrors = [];

        if (!isBdPhone($agentPhone)) {
            $errors[] = 'Agent number must be a valid BD number (01XXXXXXXXX).';
            $fieldErrors['agent'] = 'Use format 01XXXXXXXXX.';
        }

        $amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
        if ($amount <= 0) {
            $errors[] = 'Amount must be a number greater than 0.';
            $fieldErrors['amount'] = 'Enter a valid amount (> 0).';
        }

        if ($password === '' || strlen($password) < 4) {
            $errors[] = 'Password must be at least 4 characters.';
            $fieldErrors['password'] = 'Password must be at least 4 characters.';
        }

        $fee = (int)(ceil($amount / 1000) * 10);
        $total = $amount + $fee;

        if (!$errors) {
            try {
                $pdo = db();

                $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = :phone AND role = "agent" LIMIT 1');
                $stmt->execute([':phone' => $agentPhone]);
                $agent = $stmt->fetch();

                if (!$agent) {
                    $errors[] = 'Agent not found.';
                    $fieldErrors['agent'] = 'Agent not found.';
                } else {
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare('SELECT balance, password FROM users WHERE id = :id FOR UPDATE');
                    $stmt->execute([':id' => currentUserId()]);
                    $cust = $stmt->fetch();

                    if (!$cust || ($password !== (string)$cust['password'])) {
                        $pdo->rollBack();
                        $errors[] = 'Incorrect password.';
                        $fieldErrors['password'] = 'Incorrect password.';
                    } elseif ((float)$cust['balance'] < $total) {
                        $pdo->rollBack();
                        $errors[] = 'Insufficient balance for amount + fee.';
                        $fieldErrors['amount'] = 'Insufficient balance.';
                    } else {
                        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id FOR UPDATE');
                        $stmt->execute([':id' => (int)$agent['id']]);
                        if (!$stmt->fetch()) {
                            $pdo->rollBack();
                            $errors[] = 'Agent not found.';
                            $fieldErrors['agent'] = 'Agent not found.';
                        } else {
                            $stmt = $pdo->prepare('UPDATE users SET balance = balance - :total WHERE id = :id');
                            $stmt->execute([':total' => $total, ':id' => currentUserId()]);

                            $stmt = $pdo->prepare('UPDATE users SET balance = balance + :amt WHERE id = :id');
                            $stmt->execute([':amt' => $amount, ':id' => (int)$agent['id']]);

                            $stmt = $pdo->prepare('
                                INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, reference, created_at)
                                VALUES (:type, :amount, :fee, :sid, :rid, :ref, NOW())
                            ');
                            $stmt->execute([
                                ':type'   => 'cash_out',
                                ':amount' => $amount,
                                ':fee'    => $fee,
                                ':sid'    => currentUserId(),
                                ':rid'    => (int)$agent['id'],
                                ':ref'    => $agentPhone,
                            ]);

                            $pdo->commit();

                            $_SESSION['success'] =
                                'Cash out successful. Fee: ' . number_format($fee, 2) .
                                ' (Total: ' . number_format($total, 2) . ')';

                            if (custIsAjax()) {
                                custJson([
                                    'status'   => 'success',
                                    'message'  => $_SESSION['success'],
                                    'redirect' => 'index.php?url=Customer/dashboard'
                                ]);
                            }

                            header('Location: index.php?url=Customer/dashboard');
                            exit;
                        }
                    }
                }
            } catch (Throwable $e) {
                if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Server error. Please try again.';
            }
        }

        if ($errors) {
            if (custIsAjax()) {
                custJson([
                    'status'       => 'error',
                    'errors'       => $errors,
                    'field_errors' => $fieldErrors
                ]);
            }
            $_SESSION['errors'] = $errors;
            header('Location: index.php?url=Customer/cashout');
            exit;
        }
    }

    $success = flashGet('success', '');
    $errors  = flashGet('errors', []);
    require __DIR__ . '/../views/customer/cashout.php';
}

function paybill(): void {
    requireCustomerRedirect();

    $user = getCurrentCustomer();
    $balance = (float)$user['balance'];
    $providers = getProviders();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $providerIdRaw = trim($_POST['provider_id'] ?? '');
        $billNo        = trim($_POST['bill_no'] ?? '');
        $amountRaw     = trim($_POST['amount'] ?? '');
        $password      = trim((string)($_POST['password'] ?? ''));

        $errors = [];
        $fieldErrors = [];

        $providerId = ctype_digit($providerIdRaw) ? (int)$providerIdRaw : 0;
        if ($providerId <= 0) {
            $errors[] = 'Please select a bill provider.';
            $fieldErrors['provider_id'] = 'Select a provider.';
        }

        if ($billNo === '' || strlen($billNo) < 4) {
            $errors[] = 'Bill number is required (min 4 characters).';
            $fieldErrors['bill_no'] = 'Bill number min 4 characters.';
        } elseif (strlen($billNo) > 64) {
            $errors[] = 'Bill number is too long.';
            $fieldErrors['bill_no'] = 'Max 64 characters.';
        }

        $amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
        if ($amount <= 0) {
            $errors[] = 'Amount must be a number greater than 0.';
            $fieldErrors['amount'] = 'Enter a valid amount (> 0).';
        }

        if ($password === '' || strlen($password) < 4) {
            $errors[] = 'Password must be at least 4 characters.';
            $fieldErrors['password'] = 'Password must be at least 4 characters.';
        }

        if (!$errors) {
            try {
                $pdo = db();

                // Provider is stored in bill_providers (not users)
                $stmt = $pdo->prepare('SELECT id, name, provider_code FROM bill_providers WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $providerId]);
                $provider = $stmt->fetch();

                if (!$provider) {
                    $errors[] = 'Invalid provider selected.';
                    $fieldErrors['provider_id'] = 'Invalid provider.';
                } else {
                    $pdo->beginTransaction();

                    // Verify customer password (plain)
                    $stmt = $pdo->prepare('SELECT balance, password FROM users WHERE id = :id FOR UPDATE');
                    $stmt->execute([':id' => currentUserId()]);
                    $cust = $stmt->fetch();

                    if (!$cust || ($password !== (string)($cust['password'] ?? ''))) {
                        $pdo->rollBack();
                        $errors[] = 'Incorrect password.';
                        $fieldErrors['password'] = 'Incorrect password.';
                    } elseif ((float)$cust['balance'] < $amount) {
                        $pdo->rollBack();
                        $errors[] = 'Insufficient balance.';
                        $fieldErrors['amount'] = 'Insufficient balance.';
                    } else {
                        // Deduct from customer
                        $stmt = $pdo->prepare('UPDATE users SET balance = balance - :amt WHERE id = :id');
                        $stmt->execute([':amt' => $amount, ':id' => currentUserId()]);

                        // Record transaction (receiver_id NULL)
                        $stmt = $pdo->prepare(
                            'INSERT INTO transactions (type, amount, fee, sender_id, receiver_id, bill_provider_id, provider_name, provider_code, reference, created_at)
'
                          . 'VALUES (:type, :amount, :fee, :sid, NULL, :pid, :pname, :pcode, :ref, NOW())'
                        );
                        $stmt->execute([
                            ':type'   => 'pay_bill',
                            ':amount' => $amount,
                            ':fee'    => 0,
                            ':sid'    => currentUserId(),
                            ':pid'    => (int)$provider['id'],
                            ':pname'  => (string)$provider['name'],
                            ':pcode'  => (string)($provider['provider_code'] ?? ''),
                            ':ref'    => $billNo,
                        ]);

                        $pdo->commit();

                        $_SESSION['success'] = 'Bill paid successfully.';

                        if (custIsAjax()) {
                            custJson([
                                'status'   => 'success',
                                'message'  => $_SESSION['success'],
                                'redirect' => 'index.php?url=Customer/dashboard'
                            ]);
                        }

                        header('Location: index.php?url=Customer/dashboard');
                        exit;
                    }
                }
            } catch (Throwable $e) {
                if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Server error. Please try again.';
            }
        }

        if ($errors) {
            if (custIsAjax()) {
                custJson([
                    'status'       => 'error',
                    'errors'       => $errors,
                    'field_errors' => $fieldErrors
                ]);
            }
            $_SESSION['errors'] = $errors;
            header('Location: index.php?url=Customer/paybill');
            exit;
        }
    }

    $success = flashGet('success', '');
    $errors  = flashGet('errors', []);
    require __DIR__ . '/../views/customer/paybill.php';
}

function loan(): void {
    requireCustomerRedirect();

    $user = getCurrentCustomer();
    $balance = (float)$user['balance'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amountRaw    = trim($_POST['amount'] ?? '');
        $durationRaw  = trim($_POST['duration'] ?? '');
        $password     = trim((string)($_POST['password'] ?? ''));

        $errors = [];
        $fieldErrors = [];

        $amount = is_numeric($amountRaw) ? (float)$amountRaw : 0;
        if ($amount <= 0 || $amount > 2000) {
            $errors[] = 'Loan amount must be between 1 and 2000.';
            $fieldErrors['amount'] = '1 to 2000 only.';
        }

        $duration = ctype_digit($durationRaw) ? (int)$durationRaw : 0;
        if ($duration <= 0) {
            $errors[] = 'Duration must be a positive number (months).';
            $fieldErrors['duration'] = 'Enter duration in months.';
        }

        if ($password === '' || strlen($password) < 4) {
            $errors[] = 'Password must be at least 4 characters.';
            $fieldErrors['password'] = 'Password must be at least 4 characters.';
        }

        if (!$errors) {
            try {
                $pdo = db();
                $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => currentUserId()]);
                $row = $stmt->fetch();

                if (!$row || ($password !== (string)$row['password'])) {
                    $errors[] = 'Incorrect password.';
                    $fieldErrors['password'] = 'Incorrect password.';
                } else {
                    $stmt = $pdo->prepare('
                        INSERT INTO loan_requests (user_id, amount, duration_months, status, created_at)
                        VALUES (:uid, :amount, :dur, :status, NOW())
                    ');
                    $stmt->execute([
                        ':uid'    => currentUserId(),
                        ':amount' => $amount,
                        ':dur'    => $duration,
                        ':status' => 'pending',
                    ]);

                    $_SESSION['success'] = 'Loan request submitted. Status: pending.';

                    if (custIsAjax()) {
                        custJson([
                            'status'   => 'success',
                            'message'  => $_SESSION['success'],
                            'redirect' => 'index.php?url=Customer/dashboard'
                        ]);
                    }

                    header('Location: index.php?url=Customer/dashboard');
                    exit;
                }
            } catch (Throwable $e) {
                $errors[] = 'Server error. Please try again.';
            }
        }

        if ($errors) {
            if (custIsAjax()) {
                custJson([
                    'status'       => 'error',
                    'errors'       => $errors,
                    'field_errors' => $fieldErrors
                ]);
            }
            $_SESSION['errors'] = $errors;
            header('Location: index.php?url=Customer/loan');
            exit;
        }
    }

    $success = flashGet('success', '');
    $errors  = flashGet('errors', []);
    require __DIR__ . '/../views/customer/loan.php';
}
