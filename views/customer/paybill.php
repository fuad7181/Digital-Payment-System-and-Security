<?php
require __DIR__ . "/../_guard.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pay Bill</title>
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/views/assets/css/customer.css">
    <script src="/views/assets/js/ajaxForms.js" defer></script>
</head>
<body>

<div class="container">
    <h2>Pay Bill</h2>

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

    <form method="post" action="index.php?url=Customer/paybill" novalidate data-ajax="1">
        <div class="ajax-messages"></div>
        <select id="provider_id" name="provider_id">
            <option value="">-- Select Bill Provider --</option>
            <?php foreach (($providers ?? []) as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <div id="err_provider_id" class="field-error"></div>

        <input type="text" id="bill_no" name="bill_no" placeholder="Bill Number">
        <div id="err_bill_no" class="field-error"></div>

        <input type="text" id="amount" name="amount" placeholder="Amount">
        <div id="err_amount" class="field-error"></div>

        <input type="password" id="acc_password" name="password" placeholder="Account Password">
        <div id="err_password" class="field-error"></div>

        <button type="submit">Pay Bill</button>
    </form>

    <div class="back">
        <a href="index.php?url=Customer/dashboard">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
