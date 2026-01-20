<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['agent_logged_in'])) {
    $_SESSION['agent_logged_in'] = true;
    $_SESSION['agent_name'] = 'Agent';
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php?url=Auth/login");
    exit;
}

/* ✅ DEMO: multiple pending requests (later DB theke asbe) */
if (!isset($_SESSION['cashout_requests'])) {
    $_SESSION['cashout_requests'] = [
        ['id'=>1, 'user'=>'U-1001', 'amount'=>'500 BDT',  'status'=>'Pending'],
        ['id'=>2, 'user'=>'U-1002', 'amount'=>'1200 BDT', 'status'=>'Pending'],
        ['id'=>3, 'user'=>'U-1003', 'amount'=>'800 BDT',  'status'=>'Pending'],
        ['id'=>4, 'user'=>'U-1004', 'amount'=>'300 BDT',  'status'=>'Pending'],
        ['id'=>5, 'user'=>'U-1005', 'amount'=>'1500 BDT', 'status'=>'Pending'],
    ];
}

$requests = $_SESSION['cashout_requests'];

/* ✅ Approve / Reject action (per request) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $req_id = (int)($_POST['req_id'] ?? 0);
    $act    = $_POST['act'] ?? '';

    foreach ($requests as $i => $r) {
        if ($r['id'] === $req_id) {
            if ($act === 'approve') {
                $requests[$i]['status'] = 'Approved';
            } elseif ($act === 'reject') {
                $requests[$i]['status'] = 'Rejected';
            }
            break;
        }
    }

    $_SESSION['cashout_requests'] = $requests;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AGENT - Cash Out Request</title>

<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
  --green:#22c55e;
  --red:#ef4444;
  --border:rgba(255,255,255,.15);
}
*{box-sizing:border-box}
html,body{height:100%}

body{
  margin:0;
  font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;
  background:
    linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
    url("/webtech/f/icons/agent_bg.jpg");
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
  color:var(--text);
  display:flex;
  flex-direction:column;
}

/* ===== TOP BAR ===== */
.topbar{
  padding:22px 36px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
}
.brand{
  display:flex;
  gap:16px;
  align-items:center;
}
.brand-badge{
  width:52px;
  height:52px;
  border-radius:16px;
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:22px;
  font-weight:900;
  color:#06102a;
}
.brand-text{line-height:1.15}
.brand-text h1{
  margin:0;
  font-size:26px;
  font-weight:1000;
}
.brand-text h2{
  margin:4px 0 0;
  font-size:17px;
  font-weight:600;
  color:var(--muted);
}
.logout{
  padding:12px 22px;
  border-radius:14px;
  background:var(--red);
  color:#fff;
  text-decoration:none;
  font-weight:900;
}
.logout:hover{opacity:.9}

/* ===== MAIN ===== */
.container{
  flex:1;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px;
}
.card{
  width:min(980px, 95vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:30px;
}

.page-title{
  text-align:center;
  font-size:36px;
  font-weight:1000;
  margin:8px 0 18px;
  letter-spacing:1px;
  background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 0 0 28px rgba(96,165,250,.35);
}
.page-sub{
  text-align:center;
  margin:0 0 22px;
  color:var(--muted);
  font-weight:600;
  font-size:14px;
}
.divider{
  height:1px;
  width:100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  margin:12px 0 22px;
}

.list-title{
  font-weight:1000;
  margin:0 0 12px;
  font-size:18px;
}

/* request card */
.req-card{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:16px;
  padding:14px;
  margin-bottom:12px;
}
.req-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}
.req-left{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
  font-weight:900;
}
.pill{
  padding:6px 10px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(255,255,255,.06);
  font-weight:900;
}
.pill-pending{
  border-color: rgba(245,158,11,.40);
  background: rgba(245,158,11,.14);
  color:#ffe7bf;
}
.pill-ok{
  border-color: rgba(34,197,94,.35);
  background: rgba(34,197,94,.14);
  color:#bbffd2;
}
.pill-bad{
  border-color: rgba(239,68,68,.35);
  background: rgba(239,68,68,.14);
  color:#ffd0d0;
}

.actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}
.btn{
  padding:10px 16px;
  border:none;
  border-radius:12px;
  cursor:pointer;
  font-weight:900;
}
.btn-approve{background: rgba(34,197,94,.95); color:#06210f;}
.btn-reject{background: rgba(239,68,68,.95); color:#2a0606;}
.btn:hover{opacity:.95}

/* Footer */
footer{
  text-align:center;
  padding:14px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
  color:var(--muted);
  font-size:13px;
}

@media (max-width: 900px){
  .page-title{font-size:26px;}
  .brand-text h1{font-size:20px;}
  .brand-text h2{font-size:14px;}
}
</style>
</head>

<body>

<div class="topbar">
  <div class="brand">
    <div class="brand-badge">DP</div>
    <div class="brand-text">
      <h1>AGENT - Cash Out Request</h1>
      <h2>Digital Payment and Security System</h2>
    </div>
  </div>
  <a class="logout" href="?action=logout">Logout</a>
</div>

<div class="container">
  <div class="card">

    <div class="page-title">Cash Out Request</div>
    <div class="page-sub">Customer requests will appear here (Approve / Reject)</div>
    <div class="divider"></div>

    <div class="list-title">Pending Requests:</div>

    <?php foreach ($requests as $r): ?>
      <?php
        $status = $r['status'];
        $statusClass = 'pill-pending';
        if ($status === 'Approved') $statusClass = 'pill-ok';
        if ($status === 'Rejected') $statusClass = 'pill-bad';
      ?>
      <div class="req-card">
        <div class="req-row">
          <div class="req-left">
            <span>User:</span>
            <span class="pill"><?php echo htmlspecialchars($r['user']); ?></span>
            <span>|</span>
            <span>Amount:</span>
            <span class="pill"><?php echo htmlspecialchars($r['amount']); ?></span>
            <span>|</span>
            <span class="pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
          </div>

          <form method="POST" class="actions">
            <input type="hidden" name="req_id" value="<?php echo (int)$r['id']; ?>">
            <button class="btn btn-approve" type="submit" name="act" value="approve">Approve</button>
            <button class="btn btn-reject" type="submit" name="act" value="reject">Reject</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>

<footer>@Digital Payment and Security System_2026</footer>

</body>
</html>
