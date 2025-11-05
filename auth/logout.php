<?php
require_once __DIR__ . '/../functions.php';

// destroy session & keep user on a friendly logout page for a moment
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');

// You can redirect immediately (as you had) or show a brief confirmation.
// This file shows a 3s confirmation then JS redirects to login.
$loginUrl = BASE_URL . '/auth/login.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Logged out — GreenCoin</title>
  <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL . '/assets/css/logout.css') ?>">
  <style>
:root{
  --bg: #f6fbf7;
  --card: #ffffff;
  --muted: #6b7280;
  --accent: #16a34a; /* green */
  --accent-600: #15803d;
  --radius: 14px;
  --shadow: 0 10px 30px rgba(6, 95, 70, 0.06);
  --glass: rgba(255,255,255,0.6);
  --max-width: 520px;
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
}

*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  background: linear-gradient(180deg, #f0fff6 0%, var(--bg) 100%);
  color:#0f172a;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
}

.logout-wrap{
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:40px 18px;
}

/* Card */
.card{
  width:100%;
  max-width:var(--max-width);
  background:var(--card);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:36px;
  text-align:center;
  border: 1px solid rgba(22,163,74,0.06);
  transform-origin:center;
  animation: float-in 420ms cubic-bezier(.2,.9,.2,1);
}

@keyframes float-in {
  from { transform: translateY(12px) scale(.995); opacity: 0; }
  to   { transform: translateY(0) scale(1); opacity: 1; }
}

/* Icon */
.check{
  width:72px;
  height:72px;
  margin:0 auto 14px;
  display:block;
  stroke: none;
  fill: linear-gradient(180deg,var(--accent),var(--accent-600));
  /* draw a green filled circle with a white check using SVG path stroke */
}
.check path{ fill:none; stroke: green; stroke-width:2.2; stroke-linecap:round; stroke-linejoin:round; }
.check circle{
  stroke:var(--accent);
  stroke-width:2.6;
  opacity:0.95;
  fill: rgba(22,163,74,0.06);
  transform-origin:center;
  animation: pop 520ms cubic-bezier(.2,.9,.2,1);
}
@keyframes pop {
  from { transform: scale(.7); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}

/* Text */
h1{
  margin: 6px 0 6px;
  font-size:20px;
  letter-spacing:-0.2px;
  color:#05201a;
  font-weight:600;
}
.sub{
  color:var(--muted);
  margin:0 0 20px;
  font-size:14px;
}

/* Buttons */
.controls{
  display:flex;
  gap:10px;
  justify-content:center;
  margin: 14px 0 8px;
  flex-wrap:wrap;
}
.btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:10px 16px;
  border-radius:10px;
  text-decoration:none;
  font-weight:600;
  font-size:14px;
  min-width:136px;
  transition: transform 160ms ease, box-shadow 160ms ease;
  border: 1px solid transparent;
}
.btn:hover{ transform: translateY(-3px); }
.btn:active{ transform: translateY(-1px); }

.btn.primary{
  background: linear-gradient(180deg, var(--accent) 0%, var(--accent-600) 100%);
  color:#fff;
  box-shadow: 0 6px 18px rgba(22,163,74,0.18);
}
.btn.ghost{
  background: transparent;
  color: var(--accent-600);
  border-color: rgba(21,128,61,0.12);
}

/* countdown text */
.countdown{
  margin-top:12px;
  color:var(--muted);
  font-size:13px;
}

/* responsive */
@media (max-width:420px){
  .card{ padding:20px; border-radius:12px; }
  .btn{ min-width:120px; padding:9px 12px; font-size:13px; }
}

    </style>
</head>
<body>
  <main class="logout-wrap">
    <div class="card">
      <svg class="check" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="12" r="10" fill="none"/>
        <path d="M7.5 12.5l2.5 2.5L16.5 9" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>

      <h1>You've been logged out</h1>
      <p class="sub">Thanks for visiting GreenCoin. You will be redirected to the login page shortly.</p>

      <div class="controls">
        <a class="btn primary" href="<?= htmlspecialchars($loginUrl) ?>">Sign in</a>
        <a class="btn ghost" href="<?= htmlspecialchars(BASE_URL) ?>">Visit homepage</a>
      </div>

      <div class="countdown" aria-live="polite">Redirecting in <span id="sec">3</span>s…</div>
    </div>
  </main>

  <script>
    // simple countdown then redirect
    (function(){
      var secEl = document.getElementById('sec');
      var t = 3;
      var login = <?= json_encode($loginUrl) ?>;
      var iv = setInterval(function(){
        t--;
        if (t <= 0) {
          clearInterval(iv);
          window.location.href = login;
        } else {
          secEl.textContent = t;
        }
      }, 1000);
    })();
  </script>
</body>
</html>

