<?php
session_start();
require_once __DIR__ . '/../../models/helpers/auth.php';

$error = "";
$success = "";
$resetLink = "";

/**
 * Forgot Password:
 * - Enter User ID
 * - System generates a reset token (for demo/local) and shows a reset link
 * - Open the reset link to set a new password
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');

    if ($user_id === "") {
        $error = "Please enter your User ID.";
    } else {
        $user = fetch_user_by_user_id($pdo, $user_id);
        if (!$user) {
            $error = "User ID not found!";
        } else {
            // Create token
            $token = bin2hex(random_bytes(16));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

            // Mark previous tokens as used (optional cleanup)
            $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')
                ->execute([(int)$user['id']]);

            $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,?)');
            $stmt->execute([(int)$user['id'], $tokenHash, $expiresAt]);

            $success = "Reset link generated! Open the link below to set a new password.";
            $resetLink = "index.php?url=Security/reset&uid=" . urlencode($user_id) . "&token=" . urlencode($token);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password - Digital Payment and Security System</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --red:#ef4444;
  --green:#22c55e;
  --border:rgba(255,255,255,.15);
}
*{box-sizing:border-box}
html,body{height:100%}

body{
  margin:0;
  font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;
  background:
    radial-gradient(1200px 700px at 15% 20%, rgba(79,124,255,0.45), transparent 60%),
    radial-gradient(900px 600px at 85% 30%, rgba(56,189,248,0.35), transparent 55%),
    radial-gradient(700px 500px at 40% 90%, rgba(167,139,250,0.25), transparent 55%),
    linear-gradient(135deg, #060b18, #0b1635, #071022);
  color:var(--text);
  display:flex;
  flex-direction:column;
}

.container{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:24px;
}

.card{
  width:min(480px, 92vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:34px 30px;
  box-shadow: 0 20px 50px rgba(0,0,0,.55);
}

.title{
  text-align:center;
  font-size:30px;
  font-weight:1000;
  letter-spacing:1px;
  margin:0 0 6px;
  background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
}
.subtitle{
  text-align:center;
  margin:0 0 18px;
  color:var(--muted);
  font-size:14px;
  font-weight:600;
}

.alert{
  margin-bottom:14px;
  padding:12px 14px;
  border-radius:14px;
  font-weight:800;
  text-align:center;
}
.alert-error{
  border:1px solid rgba(239,68,68,.35);
  background: rgba(239,68,68,.12);
  color:#ffd0d0;
}
.alert-success{
  border:1px solid rgba(34,197,94,.35);
  background: rgba(34,197,94,.12);
  color:#c9ffe0;
}

.field{margin-bottom:14px;}
.field label{
  display:block;
  margin-bottom:6px;
  font-size:13px;
  font-weight:800;
  color:var(--muted);
}
.field input{
  width:100%;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
  color:var(--text);
  outline:none;
  font-size:14px;
}
.field input:focus{border-color: rgba(79,124,255,.7);}

.btn{
  width:100%;
  padding:12px 16px;
  border:none;
  border-radius:14px;
  cursor:pointer;
  font-weight:900;
  font-size:15px;
  background: linear-gradient(135deg, var(--accent), #8aa6ff);
  color:#06102a;
}
.btn:hover{opacity:.95}

.links{
  margin-top:16px;
  text-align:center;
  font-size:13px;
  color:var(--muted);
  font-weight:700;
}
.links a{
  color:#93c5fd;
  text-decoration:none;
  font-weight:900;
}
.links a:hover{text-decoration:underline;}

footer{
  text-align:center;
  padding:14px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.35);
  color:var(--muted);
  font-size:13px;
}
</style>
</head>

<body>
<div class="container">
  <div class="card">
    <h1 class="title">Forgot Password</h1>
    <p class="subtitle">Set a new password using your User ID</p>

    <?php if ($error !== ""): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="field">
        <label>User ID:</label>
        <input type="text" name="user_id" placeholder="Enter User ID" value="<?php echo htmlspecialchars($_POST['user_id'] ?? ''); ?>" required>
      </div>

      <button class="btn" type="submit">Generate Reset Link</button>
    </form>

    <?php if ($resetLink): ?>
      <div class="links" style="margin-top:14px;">
        <div style="font-weight:900; margin-bottom:6px;">Reset Link (demo/local):</div>
        <a href="<?php echo htmlspecialchars($resetLink); ?>"><?php echo htmlspecialchars($resetLink); ?></a>
      </div>
    <?php endif; ?>

    <div class="links">
      <a href="index.php?url=Auth/login">Back to Login</a>
    </div>
  </div>
</div>

<footer>@Digital Payment and Security System_2026</footer>
</body>
</html>
