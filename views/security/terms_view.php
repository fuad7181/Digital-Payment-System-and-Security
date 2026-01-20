<?php
session_start();

/* Read from file so it works for everyone */
$file = __DIR__ . "/terms.json";

$terms = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (is_array($data)) $terms = $data;
}

/* fallback default */
if (count($terms) === 0) {
    $terms = [
        "Users must complete account verification (KYC) before accessing full payment services.",
        "Cash In and Cash Out transactions are subject to system verification and agent approval.",
        "Any suspicious transaction may be temporarily blocked for security review.",
        "A transaction fee may apply depending on the service type and amount.",
        "Users must ensure that the receiver’s User ID / account details are correct before sending money.",
        "Wrong transfer details may cause permanent loss of funds, and the system will not be responsible.",
        "All transactions are logged for audit and compliance purposes.",
        "Loan requests are approved/rejected based on eligibility rules defined by the admin.",
        "Users must keep their password confidential; sharing credentials is strictly prohibited.",
        "Accounts involved in fraud, scam, or illegal activities may be permanently suspended.",
        "Failed transactions may take time to reverse depending on the processing network.",
        "Admin reserves the right to update Terms & Conditions at any time without prior notice."
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Terms & Conditions</title>
<style>
:root{
  --text:#eaf0ff;
  --muted:#9aa8c7;
  --accent:#4f7cff;
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
.topbar{
  padding:18px 30px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid var(--border);
  background: rgba(10,16,30,.45);
  backdrop-filter: blur(10px);
}
.brand{
  display:flex; gap:12px; align-items:center;
}
.badge{
  width:44px; height:44px; border-radius:14px;
  background: linear-gradient(135deg,var(--accent),#8aa6ff);
  display:flex; align-items:center; justify-content:center;
  font-weight:1000; color:#06102a;
}
.brand h1{margin:0; font-size:18px; font-weight:1000;}
.brand p{margin:4px 0 0; font-size:13px; color:var(--muted); font-weight:600;}
.back{
  padding:10px 16px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.16);
  background: rgba(10,16,30,.40);
  color:#cfe0ff;
  text-decoration:none;
  font-weight:900;
}
.back:hover{opacity:.95}
.container{
  flex:1;
  display:flex;
  justify-content:center;
  padding:24px;
}
.card{
  width:min(920px, 95vw);
  border:1px solid var(--border);
  border-radius:22px;
  background: rgba(15,25,45,.60);
  backdrop-filter: blur(12px);
  padding:26px;
}
.title{
  text-align:center;
  font-size:34px;
  font-weight:1000;
  letter-spacing:1px;
  margin:0 0 6px;
  background: linear-gradient(90deg, #60a5fa, #a78bfa, #34d399);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
}
.sub{
  text-align:center;
  margin:0 0 18px;
  color:var(--muted);
  font-size:14px;
  font-weight:600;
}
.divider{
  height:1px;
  width:100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  margin:12px 0 18px;
}
ol{margin:0; padding-left:22px;}
li{margin:10px 0; line-height:1.6; font-weight:700;}
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

<div class="topbar">
  <div class="brand">
    <div class="badge">DP</div>
    <div>
      <h1>Terms & Conditions</h1>
      <p>Digital Payment and Security System</p>
    </div>
  </div>
  <a class="back" href="index.php?url=Auth/login">⬅ Back to Login</a>
</div>

<div class="container">
  <div class="card">
    <div class="title">Terms & Conditions</div>
    <div class="sub">Serially showing latest terms (updated by Admin)</div>
    <div class="divider"></div>

    <ol>
      <?php foreach ($terms as $t): ?>
        <li><?php echo htmlspecialchars($t); ?></li>
      <?php endforeach; ?>
    </ol>
  </div>
</div>

<footer>@Digital Payment and Security System_2026</footer>
</body>
</html>
