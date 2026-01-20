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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AGENT - User Verification</title>

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

/* 🔥 HEADER TEXT MADE BIGGER */
.brand-text{
  line-height:1.15;
}
.brand-text h1{
  margin:0;
  font-size:26px;     /* ⬅ bigger */
  font-weight:1000;
}
.brand-text h2{
  margin:4px 0 0;
  font-size:17px;     /* ⬅ bigger */
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

/* CENTER TITLE */
.page-title{
  text-align:center;
  font-size:36px;
  font-weight:1000;
  margin:8px 0 18px;
  letter-spacing:1px;
  background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
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

/* Search */
.search-row{
  display:flex;
  gap:12px;
  align-items:center;
  flex-wrap:wrap;
}
.search-row label{font-weight:900;}
.search-row input{
  flex:1;
  min-width:240px;
  padding:12px 14px;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(10,16,30,.55);
  color:var(--text);
}
.btn{
  padding:12px 18px;
  border:none;
  border-radius:12px;
  cursor:pointer;
  font-weight:900;
}
.btn-search{
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  color:#06102a;
}

/* User info */
.section-title{
  margin:22px 0 12px;
  font-size:18px;
  font-weight:1000;
}
.info-grid{
  display:grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap:14px;
}
.field{
  border:1px solid rgba(255,255,255,.14);
  background: rgba(10,16,30,.45);
  border-radius:14px;
  padding:12px 14px;
}
.field .k{
  font-size:12px;
  color:var(--muted);
  margin-bottom:6px;
  font-weight:800;
}
.field .v{
  font-size:15px;
  font-weight:900;
  min-height:20px;
}
.full{grid-column:1/-1;}

.status-pill{
  display:inline-block;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid rgba(255,255,255,.18);
  background: rgba(255,255,255,.06);
  font-weight:900;
}

/* Actions */
.actions{
  margin-top:18px;
  display:flex;
  gap:12px;
}
.btn-approve{background:#22c55e;color:#06210f;}
.btn-reject{background:#ef4444;color:#2a0606;}

/* Footer */
footer{
  text-align:center;
  padding:14px;
  border-top:1px solid var(--border);
  background: rgba(10,16,30,.45);
  color:var(--muted);
  font-size:13px;
}

/* Responsive */
@media (max-width:900px){
  .info-grid{grid-template-columns:1fr;}
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
      <h1>AGENT - User Verification</h1>
      <h2>Digital Payment and Security System</h2>
    </div>
  </div>
  <a class="logout" href="?action=logout">Logout</a>
</div>

<div class="container">
  <div class="card">

    <div class="page-title">User Verification</div>
    <div class="page-sub">Search a User by ID & Verify Account Status</div>
    <div class="divider"></div>

    <div class="search-row">
      <label>Enter User ID:</label>
      <input type="text" placeholder="Enter User ID">
      <button class="btn btn-search" type="button">Search</button>
    </div>

    <div class="section-title">User Info:</div>

    <div class="info-grid">
      <div class="field"><div class="k">Name:</div><div class="v"></div></div>
      <div class="field"><div class="k">Mobile:</div><div class="v"></div></div>
      <div class="field"><div class="k">Gender:</div><div class="v"></div></div>
      <div class="field full"><div class="k">Address:</div><div class="v"></div></div>
      <div class="field"><div class="k">NID:</div><div class="v"></div></div>
      <div class="field"><div class="k">Profession:</div><div class="v"></div></div>
      <div class="field"><div class="k">Status:</div><div class="v"><span class="status-pill">Pending Verification</span></div></div>
    </div>

    <div class="actions">
      <button class="btn btn-approve" type="button">Approve</button>
      <button class="btn btn-reject" type="button">Reject</button>
    </div>

  </div>
</div>

<footer>@Digital Payment and Security System_2026</footer>

</body>
</html>
