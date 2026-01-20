<?php
require __DIR__ . "/../_guard.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Take Loan</title>
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/views/assets/css/customer.css">
    <script src="/views/assets/js/ajaxForms.js" defer></script>
</head>
<body>

<div class="container">
    <h2>Take Loan</h2>

    <div class="balance-box">Available Balance: <strong><?= number_format((float)($balance ?? 0), 2) ?></strong></div>
    
    <?php if (!empty($success)): ?>
        <div class="msg-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="msg-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?url=Customer/loan" novalidate data-ajax="1">
        <div class="ajax-messages"></div>
        <input type="text" id="amount" name="amount" placeholder="Loan Amount">
        <div id="err_amount" class="field-error"></div>

        <input type="text" id="duration" name="duration" placeholder="Duration (Months)">
        <div id="err_duration" class="field-error"></div>

        <input type="password" id="acc_password" name="password" placeholder="Account Password">
        <div id="err_password" class="field-error"></div>

        <button type="submit">Apply Loan</button>
    </form>

    <div class="back">
        <a href="index.php?url=Customer/dashboard">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
