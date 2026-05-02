<?php
/**
 * forgot_password.php — Step 1: enter email to receive reset OTP
 * Place in: /amazingworldmarketingcorp/auth/forgot_password.php
 */
require_once '../includes/security.php';
require_once '../includes/mailer.php';
require_once '../config/database.php';

// Already logged in — redirect away
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin'
        ? '/amazingworldmarketingcorp/admin/dashboard.php'
        : '/amazingworldmarketingcorp/customer/products.php'));
    exit;
}

$db    = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // rate_limit_check disabled temporarily for testing
        // rate_limit_increment('forgot_pw');

        $stmt = $db->prepare("SELECT user_id, name FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $otp = generate_otp();
            store_otp($db, $email, 'reset', $otp, 600);

            $sent = send_mail(
                $email,
                $user['name'],
                'Reset your Amazing World password',
                otp_email_html($otp, 'reset', 10)
            );

            if (!$sent) {
                $error = 'Failed to send email. Please try again. (Check mail_error.txt for details)';
            } else {
                $_SESSION['reset_pending'] = [
                    'email'     => $email,
                    'name'      => $user['name'],
                    'issued_at' => time(),
                ];
                header('Location: verify_reset_otp.php');
                exit;
            }
        } else {
            // Silently redirect even if email not found (security)
            $_SESSION['reset_pending'] = [
                'email'     => $email,
                'name'      => '',
                'issued_at' => time(),
            ];
            header('Location: verify_reset_otp.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password — Amazing World Marketing Corp</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans','Sora',sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px;background:linear-gradient(135deg,#0b1f3a 0%,#112d52 50%,#1a4070 100%);color:#fff}
a{text-decoration:none}
.page{display:flex;gap:48px;align-items:center;justify-content:center;width:100%;max-width:900px}
.left{flex:1;text-align:center;display:flex;flex-direction:column;align-items:center;gap:20px}
.left img{width:130px;height:130px;object-fit:contain}
.left h1{font-size:28px;font-weight:800;line-height:1.3}
.left .tagline{color:rgba(255,255,255,.55);font-size:14px}
.feature{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:14px 18px;width:100%;text-align:left}
.feature strong{display:block;font-size:14px;color:#fff;margin-bottom:3px}
.feature span{font-size:12px;color:rgba(255,255,255,.5)}
.card{background:rgba(255,255,255,.10);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.2);border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.4);padding:48px 44px;width:100%;max-width:440px;color:#fff}
.logo{display:flex;align-items:center;gap:12px;margin-bottom:32px}
.logo img{width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,.3);object-fit:cover}
.logo-name{font-weight:800;font-size:.95rem;line-height:1.1}
.logo-sub{font-size:.62rem;color:#f59e0b;font-weight:600;text-transform:uppercase;letter-spacing:.08em}
.icon{width:60px;height:60px;background:rgba(37,99,235,.2);border:2px solid rgba(37,99,235,.4);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:26px}
h2{font-size:1.7rem;font-weight:800;margin-bottom:8px;text-align:center}
.sub{color:rgba(255,255,255,.65);font-size:.9rem;line-height:1.6;margin-bottom:28px;text-align:center}
.alert{border-radius:12px;padding:12px 16px;font-size:.875rem;margin-bottom:20px}
.alert-error{background:rgba(255,241,242,.1);border:1px solid rgba(252,165,165,.4);color:#fca5a5}
label{display:block;font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:8px}
input[type="email"]{width:100%;background:rgba(255,255,255,.1);border:1.5px solid rgba(255,255,255,.2);border-radius:12px;padding:13px 16px;color:#fff;font-size:.95rem;outline:none;font-family:inherit;transition:border-color .2s,box-shadow .2s}
input[type="email"]:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.2)}
input[type="email"]::placeholder{color:rgba(255,255,255,.3)}
.btn{display:block;width:100%;padding:14px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:20px;transition:background .2s,transform .1s;font-family:inherit}
.btn:hover{background:#1d4ed8}
.btn:active{transform:scale(.98)}
.divider{height:1px;background:rgba(255,255,255,.15);margin:24px 0}
.back{text-align:center;font-size:.85rem;color:rgba(255,255,255,.55)}
.back a{color:rgba(255,255,255,.7);font-weight:600}
@media(max-width:700px){.left{display:none}}
</style>
</head>
<body>
<div class="page">

  <div class="left">
    <img src="/amazingworldmarketingcorp/images/logo.png" alt="AWMC Logo">
    <h1>Amazing World<br>Marketing Corp</h1>
    <p class="tagline">Your Success is our Business.</p>
    <div class="feature"><strong>Member Exclusives</strong><span>Access premium products only for members</span></div>
    <div class="feature"><strong>Multiple Payment Options</strong><span>GCash, Bank Transfer, Cash on Delivery</span></div>
    <div class="feature"><strong>Smart Queue System</strong><span>Real-time queue number tracking</span></div>
  </div>

  <div class="card">
    <div class="logo">
      <img src="/amazingworldmarketingcorp/images/logo.png" alt="Logo">
      <div><div class="logo-name">AMAZING WORLD</div><div class="logo-sub">MARKETING CORPORATION</div></div>
    </div>

    <div class="icon">🔑</div>
    <h2>Forgot Password</h2>
    <p class="sub">Enter your registered email and we'll send you a 6-digit reset code.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <label for="email">Email Address</label>
      <input
        type="email"
        id="email"
        name="email"
        placeholder="you@example.com"
        value="<?= e($_POST['email'] ?? '') ?>"
        required
        autofocus
      >
      <button type="submit" class="btn">Send Reset Code →</button>
    </form>

    <div class="divider"></div>
    <p class="back">Remembered it? <a href="login.php">Back to Login</a></p>
  </div>

</div>
</body>
</html>