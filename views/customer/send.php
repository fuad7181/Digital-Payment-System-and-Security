<?php
require __DIR__ . "/../_guard.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- External CSS -->
    <link rel="stylesheet" href="<?= baseUrl(); ?>/views/assets/css/customer.css">

    <!-- Ajax JS -->
    <script src="<?= baseUrl(); ?>/views/assets/js/ajaxForms.js" defer></script>
</head>
<body>

<div class="container">
    <h2>Send Money</h2>

    <div class="balance-box">
        Available Balance:
        <strong><?= number_format((float)($balance ?? 0), 2) ?> BDT</strong>
    </div>

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

    <form method="post"
          action="index.php?url=Customer/send"
          novalidate
          data-ajax="1">

        <div class="ajax-messages"></div>

        <input type="text"
               id="receiver"
               name="receiver"
               placeholder="Receiver Number (01XXXXXXXXX)">
        <div id="err_receiver" class="field-error"></div>

        <input type="text"
               id="amount"
               name="amount"
               placeholder="Amount">
        <div id="err_amount" class="field-error"></div>

        <input type="password"
               id="acc_password"
               name="password"
               placeholder="Account Password">
        <div id="err_password" class="field-error"></div>

        <button type="submit">Send</button>
    </form>

    <div class="back">
        <a href="index.php?url=Customer/dashboard">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
