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

// Refresh user data
$stmt=$db->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param('i',$userId); $stmt->execute();
$user=$stmt->get_result()->fetch_assoc(); $stmt->close();

$initials = strtoupper(substr($user['name'] ?? 'U', 0, 1));
$hasPhoto  = !empty($user['profile_photo']) && file_exists('../' . $user['profile_photo']);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile — Amazing World</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body{font-family:'Plus Jakarta Sans','Sora',sans-serif;margin:0;background:#f9fafb}
.container{max-width:800px;margin:auto;padding:24px 16px}
.card{background:#fff;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.07);margin-bottom:20px;overflow:hidden}
.card-header{padding:16px 20px;border-bottom:1px solid #e2e8f0;font-weight:700;color:#0b1f3a;font-size:1rem}
.card-body{padding:20px}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:.75rem;font-weight:700;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}
.form-control{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:.95rem;outline:none;transition:.2s;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.form-row{display:flex;gap:12px;flex-wrap:wrap}
.form-row .form-group{flex:1;min-width:200px}
.btn{padding:11px 24px;border-radius:10px;font-weight:700;cursor:pointer;border:none;font-family:inherit;font-size:.9rem;transition:.2s}
.btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
.btn-danger{background:#ef4444;color:#fff;padding:6px 14px;font-size:.8rem}.btn-danger:hover{background:#dc2626}
.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:.875rem}
.alert-success{background:#dcfce7;border:1px solid #4ade80;color:#166534}
.badge-member{background:#f59e0b;color:#fff;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700}
.badge-non{background:#e2e8f0;color:#64748b;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700}
.acc-item{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px}
.acc-icon{font-size:1.5rem;width:40px;text-align:center}

/* ── HERO ── */
.page-hero{background:linear-gradient(135deg,#0b1f3a,#1a4070);color:#fff;padding:40px 24px 52px;text-align:center;}
.avatar-ring {
  width:100px;height:100px;border-radius:50%;cursor:pointer;
  position:relative;overflow:hidden;
  border:3px solid rgba(255,255,255,.35);
  box-shadow:0 4px 24px rgba(0,0,0,.45);
  transition:transform .2s, box-shadow .2s;
  display:inline-block;
}
.avatar-ring:hover { transform:scale(1.06); box-shadow:0 6px 28px rgba(0,0,0,.55); }
.avatar-overlay {
  position:absolute;inset:0;
  background:rgba(0,0,0,.48);
  display:flex;align-items:center;justify-content:center;
  opacity:0;transition:opacity .2s;
  font-size:1.5rem;
}
.avatar-ring:hover .avatar-overlay { opacity:1; }
.remove-dot {
  position:absolute;bottom:2px;right:2px;
  width:24px;height:24px;border-radius:50%;
  background:#ef4444;border:2px solid #fff;
  color:#fff;font-size:.65rem;font-weight:800;
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:background .2s;z-index:10;
}
.remove-dot:hover { background:#dc2626; }
</style>
</head><body>
<?php include '../includes/navbar.php'; ?>

<!-- ── HERO with clickable avatar ── -->
<div class="page-hero">

  <div style="position:relative;display:inline-block;margin-bottom:14px;">

    <!-- Clickable avatar — submits form on file pick -->
    <form method="POST" enctype="multipart/form-data" id="photoForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="upload_photo">
      <input type="file" name="profile_photo" id="photoFileInput"
             accept="image/jpeg,image/png,image/webp"
             onchange="document.getElementById('photoForm').submit()" style="display:none;">

      <div class="avatar-ring" onclick="document.getElementById('photoFileInput').click()" title="Click to change photo">
        <?php if ($hasPhoto): ?>
          <img src="/amazingworldmarketingcorp/<?= htmlspecialchars($user['profile_photo']) ?>"
               alt="Profile" style="width:100%;height:100%;object-fit:cover;display:block;">
        <?php else: ?>
          <div style="width:100%;height:100%;
                      background:linear-gradient(135deg,#2e6ee6,#7c3aed);
                      display:flex;align-items:center;justify-content:center;
                      font-size:2.4rem;font-weight:800;color:#fff;">
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

  <h1 style="margin:0 0 4px;font-size:1.7rem;"><?= e($user['name']) ?></h1>
</div>

<div class="container" style="margin-top:28px">
  <?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
  <?php if (!empty($errors)): ?><div class="alert" style="background:#fff1f2;border:1px solid #fca5a5;color:#991b1b"><ul style="margin:0;padding-left:16px"><?php foreach($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <!-- Profile Info -->
  <div class="card">
    <div class="card-header">👤 Personal Information
      <span class="<?= $user['member_status']==='member'?'badge-member':'badge-non' ?>" style="float:right"><?= $user['member_status']==='member'?'⭐ Member':'Non-Member' ?></span>
    </div>
    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required maxlength="150"></div>
          <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required maxlength="150"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Contact Number</label><input type="text" name="contact_number" class="form-control" value="<?= e($user['contact_number']??'') ?>" maxlength="20"></div>
          <div class="form-group"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($user['address']??'') ?>" maxlength="500"></div>
        </div>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      </form>
    </div>
  </div>

  <!-- Change Password -->
  <div class="card">
    <div class="card-header">🔒 Change Password</div>
    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required maxlength="255"></div>
          <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required minlength="6" maxlength="255"></div>
          <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required maxlength="255"></div>
        </div>
        <button type="submit" class="btn btn-primary">🔒 Update Password</button>
      </form>
    </div>
  </div>

  <!-- Payment Accounts -->
  <div class="card">
    <div class="card-header">💳 Payment Accounts</div>
    <div class="card-body">
      <?php foreach ($accounts as $acc): ?>
      <div class="acc-item">
        <span class="acc-icon"><?= $acc['account_type']==='gcash'?'📱':'🏦' ?></span>
        <div style="flex:1">
          <div style="font-weight:600"><?= e($acc['account_name']) ?></div>
          <div style="font-size:.8rem;color:#64748b"><?= $acc['account_type']==='bank_transfer'&&$acc['bank_name']?e($acc['bank_name']).' — ':'' ?><?= e($acc['account_number']) ?></div>
        </div>
        <?php if($acc['is_default']): ?><span style="background:#fef3c7;color:#92400e;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px">Default</span><?php endif; ?>
        <form method="POST" style="margin:0"><input type="hidden" name="action" value="delete_account"><input type="hidden" name="account_id" value="<?= (int)$acc['account_id'] ?>"><?= csrf_field() ?><button type="submit" class="btn btn-danger" onclick="return confirm('Remove this account?')">Remove</button></form>
      </div>
      <?php endforeach; ?>
      <?php if (empty($accounts)): ?><p style="color:#94a3b8;font-size:.875rem">No payment accounts saved yet.</p><?php endif; ?>

      <hr style="margin:20px 0;border-color:#f1f5f9">
      <h4 style="margin-bottom:16px;color:#0b1f3a">Add Payment Account</h4>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_account">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Type</label>
            <select name="account_type" class="form-control">
              <option value="gcash">📱 GCash</option>
              <option value="bank_transfer">🏦 Bank Transfer</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Account Name</label><input type="text" name="account_name" class="form-control" placeholder="Juan dela Cruz" maxlength="150"></div>
          <div class="form-group"><label class="form-label">Account Number</label><input type="text" name="account_number" class="form-control" placeholder="09XXXXXXXXX" maxlength="50"></div>
          <div class="form-group"><label class="form-label">Bank Name (if Bank Transfer)</label><input type="text" name="bank_name" class="form-control" placeholder="BDO, BPI, etc." maxlength="100"></div>
        </div>
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:.9rem"><input type="checkbox" name="is_default"> Set as default</label>
        <button type="submit" class="btn btn-primary">➕ Add Account</button>
      </form>
    </div>
  </div>
</div>
</body></html>