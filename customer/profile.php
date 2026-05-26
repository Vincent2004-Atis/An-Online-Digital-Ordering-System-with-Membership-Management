<?php
require_once '../includes/security.php';
require_once '../middleware/auth.php';
requireCustomer();
require_once '../config/database.php';
$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param('i',$userId); $stmt->execute();
$user = $stmt->get_result()->fetch_assoc(); $stmt->close();

$stmt = $db->prepare("SELECT * FROM user_payment_accounts WHERE user_id=? ORDER BY is_default DESC");
$stmt->bind_param('i',$userId); $stmt->execute();
$accounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

$msg=''; $errors=[];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $action = clean($_POST['action']??'',20);

    if ($action==='upload_photo') {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['profile_photo'];
            $allowed  = ['image/jpeg','image/jpg','image/png','image/webp'];
            $maxSize  = 2 * 1024 * 1024;
            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Only JPG, PNG, or WEBP images are allowed.';
            } elseif ($file['size'] > $maxSize) {
                $errors[] = 'Image must be under 2MB.';
            } else {
                $uploadDir = '../uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!empty($user['profile_photo']) && file_exists('../' . $user['profile_photo'])) {
                    unlink('../' . $user['profile_photo']);
                }
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $destPath = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $dbPath = 'uploads/profiles/' . $filename;
                    $s = $db->prepare("UPDATE users SET profile_photo=? WHERE user_id=?");
                    $s->bind_param('si', $dbPath, $userId);
                    $s->execute(); $s->close();
                    $msg = '✅ Profile photo updated!';
                } else {
                    $errors[] = 'Failed to save image. Check folder permissions.';
                }
            }
        } else {
            $errors[] = 'No file selected.';
        }
    } elseif ($action==='remove_photo') {
        if (!empty($user['profile_photo']) && file_exists('../' . $user['profile_photo'])) {
            unlink('../' . $user['profile_photo']);
        }
        $s = $db->prepare("UPDATE users SET profile_photo=NULL WHERE user_id=?");
        $s->bind_param('i', $userId); $s->execute(); $s->close();
        $msg = '✅ Profile photo removed.';
    } elseif ($action==='update_profile') {
        $name    = clean($_POST['name']??'',150);
        $email   = clean($_POST['email']??'',150);
        $contact = clean($_POST['contact_number']??'',20);
        $address = clean($_POST['address']??'',500);
        if (empty($name))  $errors[]='Name required.';
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Valid email required.';
        if (empty($errors)) {
            $s=$db->prepare("UPDATE users SET name=?,email=?,contact_number=?,address=? WHERE user_id=?");
            $s->bind_param('ssssi',$name,$email,$contact,$address,$userId);
            if ($s->execute()) { $_SESSION['name']=$name; $msg='✅ Profile updated!'; }
            $s->close();
        }
    } elseif ($action==='change_password') {
        $cur  = $_POST['current_password']??'';
        $new  = $_POST['new_password']??'';
        $conf = $_POST['confirm_password']??'';
        if (!password_verify($cur,$user['password'])) $errors[]='Current password incorrect.';
        if (strlen($new)<6) $errors[]='New password must be at least 6 characters.';
        if ($new!==$conf) $errors[]='Passwords do not match.';
        if (empty($errors)) {
            $hash=password_hash($new,PASSWORD_DEFAULT);
            $s=$db->prepare("UPDATE users SET password=? WHERE user_id=?");
            $s->bind_param('si',$hash,$userId); $s->execute(); $s->close();
            $msg='✅ Password changed!';
        }
    } elseif ($action==='add_account') {
        $type   = clean($_POST['account_type']??'',20);
        $aname  = clean($_POST['account_name']??'',150);
        $anum   = clean($_POST['account_number']??'',50);
        $bank   = clean($_POST['bank_name']??'',100);
        $def    = isset($_POST['is_default'])?1:0;
        if (in_array($type,['gcash','bank_transfer']) && !empty($aname) && !empty($anum)) {
            if ($def) $db->query("UPDATE user_payment_accounts SET is_default=0 WHERE user_id=$userId");
            $s=$db->prepare("INSERT INTO user_payment_accounts (user_id,account_type,account_name,account_number,bank_name,is_default) VALUES (?,?,?,?,?,?)");
            $s->bind_param('issssi',$userId,$type,$aname,$anum,$bank,$def);
            $s->execute(); $s->close();
            $msg='✅ Payment account added!';
        }
    } elseif ($action==='delete_account') {
        $aid=(int)($_POST['account_id']??0);
        $s=$db->prepare("DELETE FROM user_payment_accounts WHERE account_id=? AND user_id=?");
        $s->bind_param('ii',$aid,$userId); $s->execute(); $s->close();
        $msg='✅ Account removed.';
    }

    if (empty($errors)) {
        header("Location: profile.php?msg=".urlencode($msg)); exit;
    }
}
if (isset($_GET['msg'])) $msg = urldecode($_GET['msg']);

$stmt=$db->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param('i',$userId); $stmt->execute();
$user=$stmt->get_result()->fetch_assoc(); $stmt->close();

$initials = strtoupper(substr($user['name'] ?? 'U', 0, 1));
$hasPhoto  = !empty($user['profile_photo']) && file_exists('../' . $user['profile_photo']);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile — Marguax Collections</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<style>
html, body {
  background: linear-gradient(to bottom right, #0e0507 0%, #1a0a0e 30%, #2a0d14 60%, #3d1020 100%) !important;
  color: #f0e6da !important;
  font-family: 'Jost', sans-serif !important;
  min-height: 100vh !important;
  margin: 0 !important;
}

.container { max-width: 820px; margin: auto; padding: 28px 24px; }

/* HERO */
.page-hero {
  background: transparent !important;
  border-bottom: 1px solid rgba(196,80,100,.15) !important;
  padding: 56px 24px 48px !important;
  text-align: center !important;
  position: relative !important;
}
.page-hero::before {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(ellipse 80% 70% at 50% -10%, rgba(196,80,100,.13) 0%, transparent 70%);
  pointer-events: none;
}
.hero-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: .68rem; font-weight: 600; letter-spacing: .28em; text-transform: uppercase;
  color: #c45064; padding: 6px 20px;
  border: 1px solid rgba(196,80,100,.3); border-radius: 40px;
  margin-bottom: 20px; background: rgba(196,80,100,.06);
  animation: heroIn .7s cubic-bezier(.16,1,.3,1) both;
}
.hero-name {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.6rem, 4vw, 2.6rem);
  font-weight: 400; color: #f0e6da; margin: 12px 0 0;
  animation: heroIn .8s .1s cubic-bezier(.16,1,.3,1) both;
}
.hero-divider {
  width: 56px; height: 1px;
  background: linear-gradient(90deg, transparent, #c45064, transparent);
  margin: 18px auto 0;
}

/* AVATAR */
.avatar-ring {
  width: 100px; height: 100px; border-radius: 50%; cursor: pointer;
  position: relative; overflow: hidden;
  border: 2px solid rgba(196,80,100,.35);
  box-shadow: 0 6px 28px rgba(0,0,0,.5);
  transition: transform .2s, box-shadow .2s;
  display: inline-block;
  animation: heroIn .8s cubic-bezier(.16,1,.3,1) both;
}
.avatar-ring:hover { transform: scale(1.06); box-shadow: 0 8px 32px rgba(196,80,100,.35); }
.avatar-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,.5);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity .2s; font-size: 1.5rem;
}
.avatar-ring:hover .avatar-overlay { opacity: 1; }
.remove-dot {
  position: absolute; bottom: 2px; right: 2px;
  width: 24px; height: 24px; border-radius: 50%;
  background: #c45064; border: 2px solid #0e0507;
  color: #fff; font-size: .6rem; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background .2s; z-index: 10;
}
.remove-dot:hover { background: #a83d53; }

/* CARD */
.card {
  background: rgba(42,13,20,.7) !important;
  border: 1px solid rgba(196,80,100,.14) !important;
  border-radius: 16px !important;
  overflow: hidden !important;
  box-shadow: none !important;
  margin-bottom: 20px !important;
  animation: fadeUp .55s cubic-bezier(.16,1,.3,1) both;
}
.card:nth-child(2) { animation-delay: .08s; }
.card:nth-child(3) { animation-delay: .14s; }
.card:nth-child(4) { animation-delay: .20s; }
.card-header {
  padding: 18px 22px;
  border-bottom: 1px solid rgba(196,80,100,.12);
  display: flex; justify-content: space-between; align-items: center;
  background: rgba(14,5,7,.3);
}
.card-header-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.05rem; font-weight: 400; color: #f0e6da;
}
.card-body { padding: 22px; }

/* FORMS */
.form-group { margin-bottom: 18px; }
.form-label {
  display: block; font-size: .65rem; font-weight: 600;
  color: #ffffff; margin-bottom: 7px;
  text-transform: uppercase; letter-spacing: .1em;
}
.form-control {
  width: 100%; padding: 11px 14px;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(196,80,100,.18);
  border-radius: 10px; font-size: .9rem;
  color: #f0e6da; outline: none;
  transition: border-color .2s, box-shadow .2s;
  font-family: 'Jost', sans-serif; box-sizing: border-box;
}
.form-control::placeholder { color: #3a2820; }
.form-control:focus { border-color: #c45064; box-shadow: 0 0 0 3px rgba(196,80,100,.12); }
select.form-control option { background: #1a0a0e; color: #f0e6da; }
.form-row { display: flex; gap: 14px; flex-wrap: wrap; }
.form-row .form-group { flex: 1; min-width: 200px; }

/* BADGES */
.badge-member { background: rgba(196,80,100,.15); color: #e8a0a8; border: 1px solid rgba(196,80,100,.4); padding: 5px 14px; border-radius: 20px; font-size: .65rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; }
.badge-non    { background: rgba(90,74,66,.25); color: #7a6058; border: 1px solid rgba(90,74,66,.35); padding: 5px 14px; border-radius: 20px; font-size: .65rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; }

/* ALERTS */
.alert { padding: 13px 16px; border-radius: 10px; margin-bottom: 18px; font-size: .875rem; }
.alert-success { background: rgba(100,196,130,.08); border: 1px solid rgba(100,196,130,.25); color: #6dbf8a; }
.alert-error   { background: rgba(196,80,100,.08); border: 1px solid rgba(196,80,100,.25); color: #e8a0a8; }

/* PAYMENT ACCOUNTS */
.acc-item {
  display: flex; align-items: center; gap: 14px;
  padding: 14px 16px;
  background: rgba(14,5,7,.3);
  border: 1px solid rgba(196,80,100,.1);
  border-radius: 12px; margin-bottom: 10px;
  transition: border-color .2s;
}
.acc-item:hover { border-color: rgba(196,80,100,.25); }
.acc-icon { font-size: 1.4rem; width: 40px; text-align: center; flex-shrink: 0; }
.acc-name { font-weight: 500; color: #f0e6da; font-size: .9rem; margin-bottom: 3px; }
.acc-detail { font-size: .78rem; color: #5a4a42; }
.badge-default { background: rgba(196,80,100,.12); color: #c45064; border: 1px solid rgba(196,80,100,.3); padding: 3px 12px; border-radius: 20px; font-size: .65rem; font-weight: 600; letter-spacing: .08em; }
.hr-divider { border: none; border-top: 1px solid rgba(196,80,100,.12); margin: 22px 0; }

/* BUTTONS */
.btn-primary {
  display: inline-flex; align-items: center; justify-content: center; gap: 6px;
  background: #c45064; color: #fff; border: none;
  border-radius: 10px; padding: 12px 28px;
  font-family: 'Jost', sans-serif; font-size: .72rem;
  font-weight: 600; letter-spacing: .14em; text-transform: uppercase;
  cursor: pointer; text-decoration: none;
  transition: background .25s, transform .2s, box-shadow .25s;
}
.btn-primary:hover { background: #a83d53; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(196,80,100,.35); }
.btn-danger {
  background: transparent; color: #7a6058; border: 1px solid rgba(196,80,100,.15);
  border-radius: 8px; padding: 7px 16px;
  font-family: 'Jost', sans-serif; font-size: .68rem;
  font-weight: 500; letter-spacing: .1em; text-transform: uppercase;
  cursor: pointer; transition: all .2s;
}
.btn-danger:hover { background: rgba(196,80,100,.1); color: #e8a0a8; border-color: rgba(196,80,100,.3); }

@keyframes heroIn { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }

@media(max-width:600px) {
  .form-row { flex-direction: column; gap: 0; }
}
</style>
</head><body>
<?php include '../includes/navbar.php'; ?>

<!-- HERO -->
<div class="page-hero">
  <div class="hero-eyebrow">Marguax Collections</div>

  <div style="position:relative;display:inline-block;margin-bottom:14px;">
    <form method="POST" enctype="multipart/form-data" id="photoForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="upload_photo">
      <input type="file" name="profile_photo" id="photoFileInput"
             accept="image/jpeg,image/png,image/webp"
             onchange="document.getElementById('photoForm').submit()" style="display:none;">

      <div class="avatar-ring" onclick="document.getElementById('photoFileInput').click()" title="Click to change photo">
        <?php if ($hasPhoto): ?>
          <img src="/Marguax_Collection/<?= htmlspecialchars($user['profile_photo']) ?>"
               alt="Profile" style="width:100%;height:100%;object-fit:cover;display:block;">
        <?php else: ?>
          <div style="width:100%;height:100%;
                      background:linear-gradient(135deg,#3d1020,#c45064);
                      display:flex;align-items:center;justify-content:center;
                      font-family:'Playfair Display',serif;font-size:2.4rem;font-weight:400;color:#f0e6da;">
            <?= $initials ?>
          </div>
        <?php endif; ?>
        <div class="avatar-overlay">📷</div>
      </div>
    </form>

    <?php if ($hasPhoto): ?>
    <form method="POST" style="margin:0;" onsubmit="return confirm('Remove your profile photo?')">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="remove_photo">
      <button type="submit" class="remove-dot" title="Remove photo">✕</button>
    </form>
    <?php endif; ?>
  </div>

  <div class="hero-name"><?= e($user['name']) ?></div>
  <div class="hero-divider"></div>
</div>

<div class="container" style="margin-top:32px">
  <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
  <?php if (!empty($errors)): ?>
  <div class="alert alert-error">
    <ul style="margin:0;padding-left:16px">
      <?php foreach($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- PERSONAL INFO -->
  <div class="card">
    <div class="card-header">
      <span class="card-header-title">Personal Information</span>
      <span class="<?= $user['member_status']==='member'?'badge-member':'badge-non' ?>">
        <?= $user['member_status']==='member'?'✦ Member':'Non-Member' ?>
      </span>
    </div>
    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required maxlength="150">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required maxlength="150">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= e($user['contact_number']??'') ?>" maxlength="20" placeholder="09XXXXXXXXX">
          </div>
          <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= e($user['address']??'') ?>" maxlength="500" placeholder="Your address">
          </div>
        </div>
        <button type="submit" class="btn-primary">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- CHANGE PASSWORD -->
  <div class="card">
    <div class="card-header">
      <span class="card-header-title">Change Password</span>
    </div>
    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required maxlength="255" placeholder="••••••••">
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6" maxlength="255" placeholder="Min. 6 characters">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required maxlength="255" placeholder="Repeat new password">
          </div>
        </div>
        <button type="submit" class="btn-primary">Update Password</button>
      </form>
    </div>
  </div>

  <!-- PAYMENT ACCOUNTS -->
  <div class="card">
    <div class="card-header">
      <span class="card-header-title">Payment Accounts</span>
      <?php if (!empty($accounts)): ?>
      <span style="background:rgba(196,80,100,.12);color:#c45064;padding:4px 14px;border-radius:20px;font-size:.65rem;font-weight:600;letter-spacing:.1em;border:1px solid rgba(196,80,100,.25)"><?= count($accounts) ?> saved</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php if (empty($accounts)): ?>
        <p style="color:#5a4a42;font-size:.875rem;font-weight:300;margin-bottom:22px">No payment accounts saved yet.</p>
      <?php else: ?>
        <?php foreach ($accounts as $acc): ?>
        <div class="acc-item">
          <span class="acc-icon"><?= $acc['account_type']==='gcash'?'📱':'🏦' ?></span>
          <div style="flex:1">
            <div class="acc-name"><?= e($acc['account_name']) ?></div>
            <div class="acc-detail"><?= $acc['account_type']==='bank_transfer'&&$acc['bank_name']?e($acc['bank_name']).' — ':'' ?><?= e($acc['account_number']) ?></div>
          </div>
          <?php if($acc['is_default']): ?><span class="badge-default">Default</span><?php endif; ?>
          <form method="POST" style="margin:0">
            <input type="hidden" name="action" value="delete_account">
            <input type="hidden" name="account_id" value="<?= (int)$acc['account_id'] ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn-danger" onclick="return confirm('Remove this account?')">Remove</button>
          </form>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <hr class="hr-divider">
      <p style="font-size:.72rem;letter-spacing:.16em;text-transform:uppercase;color:#5a4a42;margin-bottom:18px">Add Payment Account</p>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_account">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Type</label>
            <select name="account_type" class="form-control">
              <option value="gcash">📱 GCash</option>
              <option value="bank_transfer">🏦 Bank Transfer</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Account Name</label>
            <input type="text" name="account_name" class="form-control" placeholder="Juan dela Cruz" maxlength="150">
          </div>
          <div class="form-group">
            <label class="form-label">Account Number</label>
            <input type="text" name="account_number" class="form-control" placeholder="09XXXXXXXXX" maxlength="50">
          </div>
          <div class="form-group">
            <label class="form-label">Bank Name (if Bank Transfer)</label>
            <input type="text" name="bank_name" class="form-control" placeholder="BDO, BPI, etc." maxlength="100">
          </div>
        </div>
        <label style="display:flex;align-items:center;gap:10px;margin-bottom:18px;font-size:.85rem;color:#7a6058;cursor:pointer">
          <input type="checkbox" name="is_default" style="accent-color:#c45064"> Set as default
        </label>
        <button type="submit" class="btn-primary">Add Account</button>
      </form>
    </div>
  </div>

</div>
</body></html>
