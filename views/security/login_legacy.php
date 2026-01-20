<?php
session_start();

require_once __DIR__ . '/../../models/helpers/auth.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role   = $_POST['role'] ?? '';
    $userId = trim($_POST['user_id'] ?? '');
    $pass   = trim($_POST['password'] ?? '');

    if ($role === '' || $userId === '' || $pass === '') {
        $error = "Please fill in all fields.";
    } else {
        $user = fetch_user_by_user_id($pdo, $userId);

        if (!$user || $user['role'] !== $role) {
            $error = "Invalid User ID / Role.";
        } elseif ($user['status'] !== 'approved') {
            $error = "Your account is not approved yet. Please wait for Admin approval.";
        } elseif (($pass !== (string)$user['password'])) {
            $error = "Invalid password.";
        } else {
            login_user($user);

            // Backward-compatible role sessions used by existing pages
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $user['name'];
                header('Location: index.php?url=Admin/dashboard');
            } elseif ($user['role'] === 'agent') {
                $_SESSION['agent_logged_in'] = true;
                $_SESSION['agent_name'] = $user['name'];
                header('Location: index.php?url=Agent/dashboard');
            } else {
                $_SESSION['customer_logged_in'] = true;
                $_SESSION['customer_name'] = $user['name'];
                // Customer landing page in this project
                header('Location: index.php?url=Security/termsView');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Digital Payment and Security System</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --red:#ef4444;
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
  background-size: cover;
  background-position:center;
  background-repeat:no-repeat;
  background-attachment:fixed;
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

/* Role selector */
.role-box{
  display:flex;
  gap:10px;
  justify-content:center;
  margin:10px 0 18px;
}
.role-box label{ cursor:pointer; user-select:none; }
.role-box input{ display:none; }
.role-box span{
  display:inline-block;
  padding:10px 12px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.16);
  background: rgba(10,16,30,.45);
  font-weight:900;
  font-size:13px;
  color:var(--text);
  transition:.2s;
}
.role-box input:checked + span{
  background: linear-gradient(135deg, var(--accent), #8aa6ff);
  color:#06102a;
  border-color: transparent;
}

/* Error */
.alert{
  margin-bottom:14px;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(239,68,68,.35);
  background: rgba(239,68,68,.12);
  color:#ffd0d0;
  font-weight:800;
  text-align:center;
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
  margin-top:18px;
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

.terms-link{
  margin-top:10px;
  text-align:center;
}
.terms-link a{
  display:inline-block;
  padding:10px 14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.16);
  background: rgba(10,16,30,.40);
  color:#cfe0ff;
  font-weight:900;
  text-decoration:none;
}
.terms-link a:hover{opacity:.95}

footer{
  text-align:center;
  padding:14px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
  color:var(--muted);
  font-size:13px;
}
</style>
</head>

<body>

<div class="container">
  <div class="card">

    <h1 class="title">Login</h1>
    <p class="subtitle">Digital Payment and Security System</p>

    <?php if ($error !== ""): ?>
      <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="role-box">
        <label>
          <input type="radio" name="role" value="admin" <?php echo (($_POST['role'] ?? 'agent')==='admin')?'checked':''; ?>>
          <span>Admin</span>
        </label>
        <label>
          <input type="radio" name="role" value="agent" <?php echo (($_POST['role'] ?? 'agent')==='agent')?'checked':''; ?>>
          <span>Agent</span>
        </label>
        <label>
          <input type="radio" name="role" value="customer" <?php echo (($_POST['role'] ?? 'agent')==='customer')?'checked':''; ?>>
          <span>Customer</span>
        </label>
      </div>

      <div class="field">
        <label>User ID:</label>
        <input type="text" name="user_id" placeholder="Enter User ID" required>
      </div>

      <div class="field">
        <label>Password:</label>
        <input type="password" name="password" placeholder="Enter Password" required>
      </div>

      <button type="submit" class="btn">Login</button>
    </form>

    <div class="links">
      <a href="index.php?url=Security/signup">Sign Up</a>
      &nbsp; | &nbsp;
      <a href="index.php?url=Security/forgot">Forgot Password</a>
      &nbsp; | &nbsp;
      <a href="change_password.php">Change Password</a>
    </div>

    <!-- ✅ NEW -->
    <div class="terms-link">
      <a href="terms_view.php">Terms and Condition</a>
    </div>

  </div>
</div>

<footer>@Digital Payment and Security System_2026</footer>

</body>
</html>
