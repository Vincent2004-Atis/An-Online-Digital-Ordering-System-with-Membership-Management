<?php
/**
 * verify_otp.php — Step 2 of login: verify the emailed OTP
 * Place in: /amazingworldmarketingcorp/auth/verify_otp.php
 */
require_once '../includes/security.php';
require_once '../includes/mailer.php';
require_once '../config/database.php';

// Must have a pending OTP login in session
if (empty($_SESSION['otp_pending'])) {
    header('Location: login.php');
    exit;
}

// Already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin'
        ? '/amazingworldmarketingcorp/admin/dashboard.php'
        : '/amazingworldmarketingcorp/customer/products.php'));
    exit;
}

$db      = getDB();
$error   = '';
$pending = $_SESSION['otp_pending'];
$email   = $pending['email'];

// Guard: if pending data is older than 10 minutes, kill it
if (time() - $pending['issued_at'] > 600) {
    unset($_SESSION['otp_pending']);
    header('Location: login.php?reason=otp_expired');
    exit;
}

// ── Resend OTP ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend') {
    csrf_verify();

    if (rate_limit_check('otp_resend', 3, 300)) {
        $error = 'Too many resend requests. Please wait a few minutes.';
    } else {
        rate_limit_increment('otp_resend');
        $otp = generate_otp();
        store_otp($db, $email, 'login', $otp, 300);

        $sent = send_mail(
            $email,
            $pending['name'],
            'Your Amazing World sign-in code',
            otp_email_html($otp, 'login', 5)
        );

        if ($sent) {
            $_SESSION['otp_pending']['issued_at'] = time(); // reset timer
            $resent = true;
        } else {
            $error = 'Failed to resend OTP. Please try again.';
        }
    }
}

// ── Verify OTP ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    csrf_verify();

    if (rate_limit_check('otp_verify', 5, 300)) {
        $error = 'Too many incorrect attempts. Please request a new code.';
    } else {
        $otp = preg_replace('/\D/', '', $_POST['otp'] ?? ''); // digits only

        if (strlen($otp) !== 6) {
            $error = 'Please enter the 6-digit code sent to your email.';
        } elseif (verify_otp_db($db, $email, 'login', $otp)) {
            // OTP is valid — complete login
            rate_limit_clear('otp_verify');
            rate_limit_clear('otp_resend');

            session_regenerate_id(true);
            $_SESSION['user_id']       = $pending['user_id'];
            $_SESSION['name']          = $pending['name'];
            $_SESSION['role']          = $pending['role'];
            $_SESSION['member_status'] = $pending['member_status'];
            $_SESSION['last_activity'] = time();
            unset($_SESSION['otp_pending']);

            header('Location: ' . ($pending['role'] === 'admin'
                ? '/amazingworldmarketingcorp/admin/dashboard.php'
                : '/amazingworldmarketingcorp/customer/products.php'));
            exit;
        } else {
            rate_limit_increment('otp_verify');
            $error = 'Invalid or expired code. Please try again or request a new one.';
        }
    }
}

// Mask email for display: j***@gmail.com
$emailParts  = explode('@', $email);
$masked      = substr($emailParts[0], 0, 1) . str_repeat('*', max(1, strlen($emailParts[0]) - 1)) . '@' . $emailParts[1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verify Code — Amazing World Marketing Corp</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans','Sora',sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px;background:linear-gradient(135deg,#0b1f3a 0%,#112d52 50%,#1a4070 100%);color:#fff}
a{text-decoration:none;color:#2563eb;font-weight:600}
.card{background:rgba(255,255,255,.10);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.2);border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.4);padding:56px 52px;width:100%;max-width:480px;color:#fff;text-align:center}
.logo{display:flex;align-items:center;gap:12px;margin-bottom:36px;justify-content:center}
.logo-img{width:48px;height:48px;border-radius:50%;border:2px solid rgba(255,255,255,.3);object-fit:cover;flex-shrink:0}
.logo-name{font-weight:800;font-size:1rem;color:#fff;line-height:1.1;text-align:left}
.logo-sub{font-size:.65rem;color:#f59e0b;font-weight:600;text-transform:uppercase;letter-spacing:.08em}
.icon{width:64px;height:64px;background:rgba(37,99,235,.2);border:2px solid rgba(37,99,235,.4);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:28px}
h1{font-size:1.8rem;font-weight:800;margin-bottom:8px}
.subtitle{color:rgba(255,255,255,.65);font-size:.9rem;line-height:1.6;margin-bottom:32px}
.subtitle strong{color:#fff}

/* OTP input group */
.otp-group{display:flex;gap:10px;justify-content:center;margin-bottom:24px}
.otp-group input{
  width:52px;height:60px;
  text-align:center;font-size:1.5rem;font-weight:800;
  border:1.5px solid rgba(255,255,255,.3);
  border-radius:12px;
  background:rgba(255,255,255,.12);
  color:#fff;outline:none;
  transition:border-color .2s,box-shadow .2s;
  font-family:monospace;
}
.otp-group input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.2)}
.otp-group input.filled{border-color:rgba(37,99,235,.6);background:rgba(37,99,235,.15)}

.btn{display:block;width:100%;padding:14px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s,transform .1s;font-family:inherit;margin-bottom:12px}
.btn:hover{background:#1d4ed8}
.btn:active{transform:scale(.98)}
.btn-ghost{display:block;width:100%;padding:12px;background:transparent;color:rgba(255,255,255,.7);border:1.5px solid rgba(255,255,255,.2);border-radius:12px;font-size:.9rem;font-weight:600;cursor:pointer;font-family:inherit;transition:background .2s,border-color .2s}
.btn-ghost:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.35)}

.alert{border-radius:12px;padding:12px 16px;font-size:.875rem;margin-bottom:20px;text-align:left;}
.alert-error{background:rgba(255,241,242,.1);border:1px solid rgba(252,165,165,.4);color:#fca5a5;}
.alert-success{background:rgba(220,252,231,.12);border:1px solid rgba(74,222,128,.35);color:#4ade80;}

.divider{height:1px;background:rgba(255,255,255,.15);margin:20px 0}
.back{font-size:.85rem;color:rgba(255,255,255,.55)}
.back a{color:rgba(255,255,255,.7)}

.timer{font-size:.8rem;color:rgba(255,255,255,.5);margin-top:10px}
.timer span{color:#f59e0b;font-weight:700}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <img class="logo-img" src="/amazingworldmarketingcorp/images/logo.png" alt="Logo">
    <div><div class="logo-name">AMAZING WORLD</div><div class="logo-sub">MARKETING CORPORATION</div></div>
  </div>

  <div class="icon">📧</div>
  <h1>Check your email</h1>
  <p class="subtitle">
    We sent a 6-digit code to<br><strong><?= e($masked) ?></strong><br>
    Enter it below to sign in.
  </p>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error">⚠️ <?= e($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($resent)): ?>
    <div class="alert alert-success">✅ A new code has been sent to your email.</div>
  <?php endif; ?>

  <!-- OTP Form -->
  <form method="POST" id="otpForm" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="otp" id="otpHidden">

    <div class="otp-group" id="otpBoxes">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-digit" data-index="0" autofocus>
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-digit" data-index="1">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-digit" data-index="2">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-digit" data-index="3">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-digit" data-index="4">
      <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-digit" data-index="5">
    </div>

    <div class="timer">Code expires in <span id="countdown">5:00</span></div>

    <button type="submit" class="btn" style="margin-top:20px" id="verifyBtn" disabled>Verify Code</button>
  </form>

  <!-- Resend Form -->
  <form method="POST" id="resendForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="resend">
    <button type="submit" class="btn-ghost" id="resendBtn">Resend Code</button>
  </form>

  <div class="divider"></div>
  <p class="back">Wrong account? <a href="login.php">Go back</a></p>
</div>

<script>
(function(){
  var digits   = document.querySelectorAll('.otp-digit');
  var hidden   = document.getElementById('otpHidden');
  var verifyBtn= document.getElementById('verifyBtn');

  function getOtp(){
    return Array.from(digits).map(d=>d.value).join('');
  }

  function updateHidden(){
    var otp = getOtp();
    hidden.value = otp;
    verifyBtn.disabled = otp.length < 6;
    digits.forEach(function(d){
      d.classList.toggle('filled', d.value !== '');
    });
  }

  digits.forEach(function(input, i){
    input.addEventListener('input', function(){
      // Accept only digits
      this.value = this.value.replace(/\D/,'');
      if(this.value && i < digits.length-1) digits[i+1].focus();
      updateHidden();
    });

    input.addEventListener('keydown', function(e){
      if(e.key==='Backspace' && !this.value && i>0){
        digits[i-1].focus();
        digits[i-1].value='';
        updateHidden();
      }
    });

    input.addEventListener('paste', function(e){
      e.preventDefault();
      var pasted = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'');
      pasted.split('').slice(0,6).forEach(function(ch, j){
        if(digits[j]) digits[j].value = ch;
      });
      var next = Math.min(pasted.length, 5);
      digits[next].focus();
      updateHidden();
    });
  });

  // Countdown timer (5 min from page load)
  var ttl  = <?= ($pending['issued_at'] + 300) - time() ?>;
  var el   = document.getElementById('countdown');
  var tick = setInterval(function(){
    ttl--;
    if(ttl <= 0){
      clearInterval(tick);
      el.textContent = '0:00';
      el.style.color = '#fca5a5';
      verifyBtn.disabled = true;
      return;
    }
    var m = Math.floor(ttl/60), s = ttl%60;
    el.textContent = m+':'+(s<10?'0':'')+s;
  }, 1000);

  if(ttl>0){
    var m=Math.floor(ttl/60),s=ttl%60;
    el.textContent=m+':'+(s<10?'0':'')+s;
  }
})();
</script>
</body>
</html>
