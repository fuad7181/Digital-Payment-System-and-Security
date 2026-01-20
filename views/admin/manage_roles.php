<?php
require __DIR__ . "/../_guard.php";
require_once __DIR__ . '/../../models/helpers/auth.php';

// Require admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php?url=Auth/login');
    exit;
}


$success = "";
$error = "";

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $userPk = (int)($_POST['pk'] ?? 0);
    $newRole = $_POST['role'] ?? '';
    $newStatus = $_POST['status'] ?? '';

    $allowedRoles = ['admin','agent','customer'];
    $allowedStatus = ['pending','approved','rejected'];

    if ($userPk <= 0) {
        $error = 'Invalid user.';
    } elseif (!in_array($newRole, $allowedRoles, true)) {
        $error = 'Invalid role.';
    } elseif (!in_array($newStatus, $allowedStatus, true)) {
        $error = 'Invalid status.';
    } else {
        // prevent demoting the seeded admin accidentally
        if ($userPk === (int)($_SESSION['user']['id'] ?? 0) && $newRole !== 'admin') {
            $error = 'You cannot remove your own admin role.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET role=?, status=? WHERE id=?');
            $stmt->execute([$newRole, $newStatus, $userPk]);
            $success = 'User updated successfully!';
        }
    }
}

// Load users
$stmt = $pdo->query('SELECT id, user_id, name, email, role, status, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Manage Roles</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --border:rgba(255,255,255,.15);
  --green:#22c55e;
}
*{box-sizing:border-box}
html,body{height:100%}

body{
  margin:0;
  font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;
  background:
    radial-gradient(1000px 600px at 20% 20%, rgba(79,124,255,.45), transparent 60%),
    radial-gradient(800px 500px at 80% 30%, rgba(167,139,250,.35), transparent 55%),
    linear-gradient(135deg, #050b18, #0b1635, #060f22);
  color:var(--text);
  display:flex;
  flex-direction:column;
}

/* TOPBAR */
.topbar{
  padding:22px 36px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
}
.brand{display:flex; gap:14px; align-items:center;}
.badge{
  width:52px; height:52px; border-radius:16px;
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  display:flex; align-items:center; justify-content:center;
  font-weight:1000; font-size:22px; color:#06102a;
}
.brand-text h1{margin:0; font-size:24px; font-weight:1000;}
.brand-text p{margin:4px 0 0; color:var(--muted); font-weight:600; font-size:14px;}

.btn{
  padding:10px 16px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.16);
  background: rgba(10,16,30,.40);
  color:var(--text);
  text-decoration:none;
  font-weight:900;
  cursor:pointer;
}
.btn:hover{opacity:.95}
.btn-primary{
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  border-color:transparent;
  color:#06102a;
}

/* MAIN */
.container{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:24px;
}
.card{
  width:min(980px, 95vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:26px;
}
.title{font-size:22px; font-weight:1000; margin:0 0 6px;}
.sub{margin:0 0 16px; color:var(--muted); font-weight:600; font-size:14px;}
.divider{
  height:1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  margin:14px 0 18px;
}

.alert{
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(34,197,94,.35);
  background: rgba(34,197,94,.12);
  color:#c9ffe0;
  font-weight:900;
  margin-bottom:14px;
}

.table-wrap{overflow:auto; border-radius:16px; border:1px solid rgba(255,255,255,.14);}
table{width:100%; border-collapse:collapse; min-width:760px;}
th,td{padding:12px 14px; text-align:center; border-bottom:1px solid rgba(255,255,255,.10);}
th{
  background: rgba(10,16,30,.55);
  font-weight:1000;
  color:#cfe0ff;
}
tr:hover td{background: rgba(255,255,255,.03);}

select{
  padding:10px 12px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
  color:var(--text);
  font-weight:900;
  outline:none;
}
select:focus{border-color: rgba(79,124,255,.7);}

.footer{
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

<div class="topbar">
  <div class="brand">
    <div class="badge">DP</div>
    <div class="brand-text">
      <h1>Admin - Manage Roles</h1>
      <p>Digital Payment and Security System</p>
    </div>
  </div>
  <a class="btn" href="index.php?url=Admin/dashboard">⬅ Back</a>
</div>

<div class="container">
  <div class="card">
    <div class="title">User Role Management</div>
    <div class="sub">Change user role to Admin / Agent / Customer.</div>
    <div class="divider"></div>

	    <?php if ($error): ?>
	      <div class="alert" style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.12); color:#ffd0d0;">
	        <?php echo htmlspecialchars($error); ?>
	      </div>
	    <?php endif; ?>

	    <?php if ($success): ?>
	      <div class="alert"><?php echo htmlspecialchars($success); ?></div>
	    <?php endif; ?>

    <div class="table-wrap">
      <table>
        <tr>
          <th>#</th>
          <th>User ID</th>
          <th>Name</th>
          <th>Role</th>
          <th>Status</th>
          <th>Update</th>
        </tr>

        <?php foreach ($users as $idx => $u): ?>
          <tr>
            <form method="post">
              <td><?php echo $idx+1; ?></td>
              <td><?php echo htmlspecialchars($u['user_id']); ?></td>
              <td><?php echo htmlspecialchars($u['name']); ?></td>
	              <td>
	                <input type="hidden" name="pk" value="<?php echo (int)$u['id']; ?>">
                <select name="role">
                  <option value="admin" <?php echo ($u['role']==='admin')?'selected':''; ?>>Admin</option>
                  <option value="agent" <?php echo ($u['role']==='agent')?'selected':''; ?>>Agent</option>
                  <option value="customer" <?php echo ($u['role']==='customer')?'selected':''; ?>>Customer</option>
                </select>
              </td>
              <td>
                <select name="status">
                  <option value="pending" <?php echo ($u['status']==='pending')?'selected':''; ?>>Pending</option>
                  <option value="approved" <?php echo ($u['status']==='approved')?'selected':''; ?>>Approved</option>
                  <option value="rejected" <?php echo ($u['status']==='rejected')?'selected':''; ?>>Rejected</option>
                </select>
              </td>
	              <td>
	                <button class="btn btn-primary" type="submit" name="update">Save</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>

      </table>
    </div>

  </div>
</div>

<div class="footer">@Digital Payment and Security System_2026</div>

</body>
</html>
