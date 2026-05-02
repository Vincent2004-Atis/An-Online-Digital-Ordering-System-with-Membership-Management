<?php
require_once '../includes/security.php';
require_once '../includes/mailer.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin'
        ? '/amazingworldmarketingcorp/admin/dashboard.php'
        : '/amazingworldmarketingcorp/customer/products.php'));
    exit;
}

require_once '../config/database.php';
$db    = getDB();
$error = '';

// ── Step 1: Email + Password ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '1') {
    csrf_verify();

    if (rate_limit_check('login')) {
        $error = 'Too many login attempts. Please wait 5 minutes and try again.';
    } else {
        $email    = clean($_POST['email'] ?? '', 150);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Please enter your email and password.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            $stmt = $db->prepare("SELECT user_id, name, role, password, member_status FROM users WHERE email=? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                rate_limit_clear('login');

                // Generate OTP and email it
                $otp = generate_otp();
                store_otp($db, $email, 'login', $otp, 300); // 5-minute expiry

                $sent = send_mail(
                    $email,
                    $user['name'],
                    'Your Amazing World sign-in code',
                    otp_email_html($otp, 'login', 5)
                );

                if ($sent) {
                    // Store minimal data in session to complete login after OTP
                    $_SESSION['otp_pending'] = [
                        'user_id'       => $user['user_id'],
                        'name'          => $user['name'],
                        'role'          => $user['role'],
                        'member_status' => $user['member_status'],
                        'email'         => $email,
                        'issued_at'     => time(),
                    ];
                    header('Location: verify_otp.php');
                    exit;
                } else {
                    $error = 'Failed to send OTP email. Please try again or contact support.';
                }
            } else {
                rate_limit_increment('login');
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Amazing World Marketing Corp</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans','Sora',sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px;background:linear-gradient(135deg,#0b1f3a 0%,#112d52 50%,#1a4070 100%);color:#fff}
a{text-decoration:none}
.auth-wrapper{display:flex;flex-direction:column;gap:40px;width:100%;max-width:1200px;}
@media(min-width:900px){.auth-wrapper{flex-direction:row;gap:120px;justify-content:center;align-items:flex-start}}
.left{display:flex;flex-direction:column;align-items:center;justify-content:center;max-width:400px}
.left-inner{text-align:center;max-width:400px}
.left-logo{width:130px;height:130px;border-radius:50%;border:4px solid rgba(255,255,255,.25);margin:0 auto 20px;display:block;object-fit:cover;box-shadow:0 0 0 8px rgba(255,255,255,.06),0 16px 48px rgba(0,0,0,.4)}
.left-inner h2{font-size:2rem;font-weight:800;margin:0 0 12px;line-height:1.2}
.left-inner p{color:rgba(255,255,255,.7);font-size:.95rem;line-height:1.7}
.features{margin-top:36px;display:flex;flex-direction:column;gap:14px}
.feature{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:8px;background:rgba(255,255,255,.06);border-radius:12px;padding:14px 18px;transition:background .2s}
.feature:hover{background:rgba(255,255,255,.12)}
.feature-text strong{color:#fff;font-size:.875rem;display:block;margin-bottom:2px;text-align:center}
.feature-text span{color:rgba(255,255,255,.6);font-size:.8rem;text-align:center}
.right{display:flex;align-items:center;justify-content:center;max-width:580px}
.card{background:rgba(255,255,255,0.10);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.2);border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.4);padding:60px 56px;width:100%;max-width:560px;color:#fff}
.logo{display:flex;align-items:center;gap:12px;margin-bottom:28px}
.logo-img{width:48px;height:48px;border-radius:50%;border:2px solid #e2e8f0;object-fit:cover;flex-shrink:0}
.logo-name{font-weight:800;font-size:1rem;color:#fff;line-height:1.1}
.logo-sub{font-size:.65rem;color:#f59e0b;font-weight:600;text-transform:uppercase;letter-spacing:.08em}
h1{font-size:2.2rem;font-weight:800;color:#fff;margin-bottom:6px}
.subtitle{color:rgba(255,255,255,0.7);font-size:.9rem;margin-bottom:28px}
.form-group{margin-bottom:18px}
label{display:block;font-size:.75rem;font-weight:700;color:rgba(255,255,255,0.85);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em}
input[type=email],input[type=password],input[type=text]{width:100%;padding:12px 14px;border:1.5px solid rgba(255,255,255,0.3);border-radius:12px;font-size:.95rem;transition:border-color .2s,box-shadow .2s;outline:none;background:rgba(255,255,255,0.15);color:#fff;font-family:inherit}
input::placeholder{color:rgba(255,255,255,.4)}
input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.pw-wrap{position:relative;display:block}
.pw-wrap input{padding-right:44px !important;width:100% !important;box-sizing:border-box}
.show-hide{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.8);cursor:pointer;padding:4px;display:flex;align-items:center;justify-content:center;transition:color .2s;line-height:0}
.show-hide:hover{color:#fff}
.show-hide svg{display:block;flex-shrink:0}
.forgot{display:block;text-align:center;font-size:.78rem;color:rgba(255,255,255,.5);margin-top:12px;transition:color .2s}
.forgot:hover{color:#fff}
.btn{display:block;width:100%;padding:14px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s,transform .1s;font-family:inherit;text-align:center;margin-top:8px}
.btn:hover{background:#1d4ed8}
.btn:active{transform:scale(.98)}
.divider{height:1px;background:rgba(255,255,255,0.2);margin:24px 0}
.bottom{text-align:center;font-size:.875rem;color:rgba(255,255,255,0.7)}
.bottom a{color:#2563eb;font-weight:600}
.alert{border-radius:12px;padding:12px 16px;font-size:.875rem;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
.alert-error{background:#fff1f2;border:1px solid #fecaca;color:#991b1b;}
.alert-info{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}
.alert-success{background:rgba(220,252,231,.15);border:1px solid rgba(74,222,128,.4);color:#4ade80;}
</style>
</head>
<body>
<div class="auth-wrapper">
  <div class="left">
    <div class="left-inner">
      <img class="left-logo" src="/amazingworldmarketingcorp/images/logo.png" alt="Amazing World Logo">
      <h2>Amazing World<br>Marketing Corp</h2>
      <p>Your Success is our Business.</p>
      <div class="features">
        <div class="feature"><div class="feature-text"><strong>Member Exclusives</strong><span>Access premium products only for members</span></div></div>
        <div class="feature"><div class="feature-text"><strong>Multiple Payment Options</strong><span>GCash, Bank Transfer, Cash on Delivery</span></div></div>
        <div class="feature"><div class="feature-text"><strong>Smart Queue System</strong><span>Real-time queue number tracking</span></div></div>
      </div>
    </div>
  </div>
  <div class="right">
    <div class="card">
      <div class="logo">
        <img class="logo-img" src="/amazingworldmarketingcorp/images/logo.png" alt="Logo">
        <div><div class="logo-name">AMAZING WORLD</div><div class="logo-sub">MARKETING CORPORATION</div></div>
      </div>

      <h1>Welcome back</h1>
      <p class="subtitle">Sign in to your account to continue.</p>

      <?php if (isset($_GET['reason']) && $_GET['reason'] === 'timeout'): ?>
        <div class="alert alert-info">⏱️ Your session expired due to inactivity. Please log in again.</div>
      <?php endif; ?>

      <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
        <div class="alert alert-success">✅ Account created successfully! Please sign in below.</div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="step" value="1">

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="your@email.com" required maxlength="150" autofocus>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="pw-wrap">
            <input type="password" name="password" id="passField" placeholder="Enter your password" required maxlength="255">
            <button type="button" class="show-hide" onclick="togglePass()" aria-label="Toggle password">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn">Continue →</button>
        <a href="forgot_password.php" class="forgot">Forgot password?</a>
      </form>

      <div class="divider"></div>
      <p class="bottom">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
  </div>
</div>
<script>
function togglePass() {
  var f = document.getElementById('passField');
  var show = f.type === 'password';
  f.type = show ? 'text' : 'password';
  document.getElementById('eyeIcon').style.display    = show ? 'none' : '';
  document.getElementById('eyeOffIcon').style.display = show ? '' : 'none';
}
</script>
</body>
</html>