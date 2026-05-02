<?php
/**
 * register.php — Two-step registration with email OTP verification
 * Step 1: Fill form → validate → send OTP
 * Step 2: Enter OTP → create account
 * Place in: /amazingworldmarketingcorp/auth/register.php
 */
require_once '../includes/security.php';
require_once '../includes/mailer.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../customer/products.php');
    exit;
}

require_once '../config/database.php';
$db     = getDB();
$errors = [];

// ── Step 1: Validate form and send OTP ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === '1') {
    csrf_verify();

    if (rate_limit_check('register', 5, 600)) {
        $errors[] = 'Too many registration attempts. Please wait 10 minutes.';
    } else {
        $name    = clean($_POST['name'] ?? '', 150);
        $email   = clean($_POST['email'] ?? '', 150);
        $contact = clean($_POST['contact'] ?? '', 20);
        $pass    = $_POST['password'] ?? '';
        $conf    = $_POST['confirm_password'] ?? '';

        if (empty($name))                               $errors[] = 'Full name is required.';
        if (strlen($name) < 2)                          $errors[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (empty($contact))                            $errors[] = 'Contact number is required.';
        if (!valid_phone($contact))                     $errors[] = 'Contact number must be in format 09XXXXXXXXX.';
        if (strlen($pass) < 6)                          $errors[] = 'Password must be at least 6 characters.';
        if (strlen($pass) > 255)                        $errors[] = 'Password is too long.';
        if ($pass !== $conf)                            $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            // Check if email already registered
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email=?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) $errors[] = 'Email already registered.';
            $stmt->close();
        }

        if (empty($errors)) {
            rate_limit_increment('register');

            // Send OTP — account is NOT created yet
            $otp  = generate_otp();
            store_otp($db, $email, 'register', $otp, 600); // 10-minute expiry

            $sent = send_mail(
                $email,
                $name,
                'Verify your email — Amazing World Marketing Corp',
                otp_email_html($otp, 'register', 10)
            );

            if ($sent) {
                // Store form data in session (we'll use it to create the account after OTP)
                $_SESSION['reg_pending'] = [
                    'name'       => $name,
                    'email'      => $email,
                    'contact'    => $contact,
                    'pass_hash'  => password_hash($pass, PASSWORD_DEFAULT),
                    'issued_at'  => time(),
                ];
                // Redirect to OTP verification page
                header('Location: verify_email.php');
                exit;
            } else {
                $errors[] = 'Failed to send verification email. Please try again.';
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
<title>Register — Amazing World Marketing Corp</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans','Sora',sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px;background:linear-gradient(135deg,#0b1f3a 0%,#112d52 50%,#1a4070 100%);color:#fff}
a{text-decoration:none}
.auth-wrapper{display:flex;flex-direction:column;gap:40px;width:100%;max-width:1200px;}
@media(min-width:900px){.auth-wrapper{flex-direction:row;gap:100px;justify-content:center;align-items:flex-start;padding-top:40px;}}
.left{display:flex;flex-direction:column;align-items:center;justify-content:center;max-width:400px}
.left-inner{text-align:center;max-width:400px}
.left-logo{width:130px;height:130px;border-radius:50%;border:4px solid rgba(255,255,255,.25);margin:0 auto 20px;display:block;object-fit:cover;box-shadow:0 0 0 8px rgba(255,255,255,.06),0 16px 48px rgba(0,0,0,.4)}
.left-inner h2{font-size:2rem;font-weight:800;margin:0 0 12px;line-height:1.2}
.left-inner p{color:rgba(255,255,255,.7);font-size:.95rem;line-height:1.7}
.features{margin-top:36px;display:flex;flex-direction:column;gap:14px}
.feature{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:8px;background:rgba(255,255,255,.06);border-radius:12px;padding:14px 18px;transition:background .2s}
.feature:hover{background:rgba(255,255,255,.12)}
.feature-text strong{color:#fff;font-size:.875rem;display:block;margin-bottom:2px}
.feature-text span{color:rgba(255,255,255,.6);font-size:.8rem}
.right{display:flex;align-items:center;justify-content:center;width:100%;max-width:520px}
.card{background:rgba(255,255,255,.10);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.2);border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.4);padding:48px 44px;width:100%;color:#fff}
.logo{display:flex;align-items:center;gap:12px;margin-bottom:28px}
.logo-img{width:48px;height:48px;border-radius:50%;border:2px solid rgba(255,255,255,.3);object-fit:cover;flex-shrink:0}
.logo-name{font-weight:800;font-size:1rem;color:#fff;line-height:1.1}
.logo-sub{font-size:.65rem;color:#f59e0b;font-weight:600;text-transform:uppercase;letter-spacing:.08em}
h1{font-size:2rem;font-weight:800;color:#fff;margin-bottom:6px}
.subtitle{color:rgba(255,255,255,.65);font-size:.9rem;margin-bottom:24px}
.alert{border-radius:12px;padding:12px 16px;font-size:.875rem;margin-bottom:18px;display:flex;align-items:flex-start;gap:8px;}
.alert-danger{background:rgba(255,241,242,.1);border:1px solid rgba(252,165,165,.4);color:#fca5a5;}
.form-group{margin-bottom:16px}
.form-row{display:flex;gap:12px;flex-wrap:wrap}
.form-row .form-group{flex:1;min-width:140px}
label{display:block;font-size:.75rem;font-weight:700;color:rgba(255,255,255,.8);margin-bottom:7px;text-transform:uppercase;letter-spacing:.04em}
input[type=text],input[type=email],input[type=password]{width:100%;padding:12px 14px;border:1.5px solid rgba(255,255,255,.25);border-radius:12px;font-size:.95rem;transition:border-color .2s,box-shadow .2s;outline:none;background:rgba(255,255,255,.12);color:#fff;font-family:inherit}
input::placeholder{color:rgba(255,255,255,.4)}
input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.2)}
.btn{display:block;width:100%;padding:14px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s,transform .1s;margin-top:8px;font-family:inherit;text-align:center}
.btn:hover{background:#1d4ed8}
.btn:active{transform:scale(.98)}
.divider{height:1px;background:rgba(255,255,255,.15);margin:24px 0}
.bottom{text-align:center;font-size:.875rem;color:rgba(255,255,255,.6)}
.bottom a{color:#2563eb;font-weight:600}
.step-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(37,99,235,.2);border:1px solid rgba(37,99,235,.4);border-radius:20px;padding:6px 14px;font-size:.78rem;font-weight:600;color:rgba(255,255,255,.8);margin-bottom:20px}
.step-dot{width:8px;height:8px;border-radius:50%;background:#2563eb}
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

      <div class="step-badge"><span class="step-dot"></span> Step 1 of 2 — Your details</div>

      <h1>Create Account</h1>
      <p class="subtitle">Fill in your details. We'll verify your email next.</p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          ⚠️
          <ul style="margin:0;padding-left:16px;">
            <?php foreach($errors as $err): ?>
              <li><?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="step" value="1">

        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required placeholder="Juan dela Cruz" maxlength="150" autofocus>
        </div>
        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required placeholder="your@email.com" maxlength="150">
        </div>
        <div class="form-group">
          <label>Contact Number *</label>
          <input type="text" name="contact" value="<?= e($_POST['contact'] ?? '') ?>" required placeholder="09XXXXXXXXX" maxlength="20">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" id="passField" required minlength="6" maxlength="255" placeholder="Min. 6 characters">
          </div>
          <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" name="confirm_password" id="confField" required maxlength="255" placeholder="Re-enter password">
          </div>
        </div>
        <button type="submit" class="btn">Send Verification Code →</button>
      </form>

      <div class="divider"></div>
      <p class="bottom">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
  </div>
</div>
</body>
</html>
