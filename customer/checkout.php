<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/database.php';
$db     = getDB();
$userId = (int)$_SESSION['user_id'];

if (empty($_SESSION['cart'])) { header('Location: cart.php'); exit; }

// Fetch user
$stmt = $db->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch saved payment accounts
$stmt = $db->prepare("SELECT * FROM user_payment_accounts WHERE user_id=? ORDER BY is_default DESC, account_id ASC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$accounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$cart  = $_SESSION['cart'];
$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['customer_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $method  = $_POST['order_method'] ?? 'pickup';
    $payment = $_POST['payment_method'] ?? 'cash_on_pickup';
    $accId   = !empty($_POST['payment_account_id']) ? (int)$_POST['payment_account_id'] : null;

    if (empty($name))    $errors[] = 'Customer name is required.';
    if (empty($address)) $errors[] = 'Address is required.';
    if (empty($contact)) $errors[] = 'Contact number is required.';
    if (!in_array($method, ['pickup','shipping'])) $errors[] = 'Invalid order method.';
    if (!in_array($payment, ['cash_on_pickup','cash_on_delivery','gcash','paymaya'])) $errors[] = 'Invalid payment method.';
    if ($method === 'pickup' && $payment === 'cash_on_delivery') $errors[] = 'Cash on Delivery is only available for Shipping orders.';

    if (in_array($payment, ['gcash','paymaya']) && $accId !== null) {
        $s = $db->prepare("SELECT account_id FROM user_payment_accounts WHERE account_id=? AND user_id=? AND account_type=?");
        $s->bind_param('iis', $accId, $userId, $payment);
        $s->execute();
        if ($s->get_result()->num_rows === 0) { $accId = null; }
        $s->close();
    } else {
        $accId = null;
    }

    if (empty($errors)) {
        $db->begin_transaction();
        try {
            $res = $db->query("SELECT IFNULL(MAX(queue_number),100)+1 AS next_q FROM orders");
            $queueNum = (int)$res->fetch_assoc()['next_q'];

            $s = $db->prepare("INSERT INTO orders (user_id,customer_name,address,contact_number,order_method,payment_method,payment_account_id,payment_status,queue_number,total_amount,order_status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $payStatus   = 'pending';
            $orderStatus = 'pending';
            $s->bind_param('isssssiidss', $userId, $name, $address, $contact, $method, $payment, $accId, $payStatus, $queueNum, $total, $orderStatus);
            $s->execute();
            $orderId = $db->insert_id;
            $s->close();

            $s = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
            foreach ($cart as $pid => $item) {
                $s->bind_param('iiid', $orderId, $pid, $item['qty'], $item['price']);
                $s->execute();
                $db->query("UPDATE products SET stock = stock - " . (int)$item['qty'] . " WHERE product_id=" . (int)$pid . " AND stock >= " . (int)$item['qty']);
            }
            $s->close();

            $db->commit();
            $_SESSION['cart'] = [];
            header("Location: order_confirmation.php?order_id=$orderId");
            exit;
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = 'Order failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Checkout — OrderSync</title>
<link rel="stylesheet" href="../css/style.css">
<style>
:root {
  --primary: #1a3c5e;
  --primary-light: #2563a8;
  --border: #ddd;
  --radius: 12px;
  --text-2: #555;
  --text-3: #888;
  --blue: #2563a8;
  --transition: 0.2s ease;
}
body { font-family: 'Plus Jakarta Sans','Sora',sans-serif; margin:0; }
.container { max-width:1100px; margin: auto; padding: 16px; }
.card { border:1px solid var(--border); border-radius:var(--radius); background:white; margin-bottom:16px; overflow:hidden; }
.card-header { padding:12px 16px; border-bottom:1px solid var(--border); font-weight:600; }
.card-body { padding:16px; }
.card-footer { padding:16px; border-top:1px solid var(--border); }
.checkout-grid { display:flex; gap:16px; flex-wrap:wrap; }
.checkout-grid > div { flex:1 1 300px; }
.payment-option-label { display:flex; align-items:center; gap:12px; padding:12px; border:1.5px solid var(--border); border-radius:var(--radius); cursor:pointer; transition:all var(--transition); margin-bottom:8px; }
.payment-option-label:hover { background:var(--primary-light); color:white; }
.payment-option { display:none; }
.payment-option:checked + .payment-option-label { border-color:var(--blue); background:#eff6ff; }
.order-item-row { display:flex; align-items:center; gap:16px; padding:12px 0; border-bottom:1px solid var(--border); }
.order-item-img { width:64px; height:64px; border-radius:8px; object-fit:cover; flex-shrink:0; }
.order-item-info { flex:1; }
.order-item-name { font-weight:600; }
.order-item-qty, .order-item-price { color:var(--text-2); font-size:.875rem; }
.total-row.grand { display:flex; justify-content:space-between; font-weight:700; font-size:1.1rem; margin-top:12px; }
.btn { padding:14px; border-radius:12px; font-weight:600; cursor:pointer; transition:all var(--transition); display:block; text-align:center; text-decoration:none; }
.btn-primary { background:var(--primary); color:white; border:none; }
.btn-primary:hover { background:var(--primary-light); }
.btn-ghost { background:none; border:1px solid var(--primary); color:var(--primary); }
.btn-ghost:hover { background:var(--primary); color:white; }
.alert-danger { background:#fff1f2; border:1px solid #fca5a5; color:#991b1b; padding:12px 16px; border-radius:12px; margin-bottom:16px; font-size:.875rem; }
.account-radio-label { display:flex; align-items:center; gap:12px; padding:12px; border:1.5px solid var(--border); border-radius:var(--radius); cursor:pointer; margin-bottom:8px; }
@media(max-width:768px) { .checkout-grid { flex-direction:column; } }
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="page-hero">
  <div class="page-hero-inner">
    <h1>Checkout</h1>
    <p>Complete your order information</p>
  </div>
</div>

<div class="container section">
  <?php if (!empty($errors)): ?>
    <div class="alert-danger">
      ⚠️ <ul style="margin:0;padding-left:16px;"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="POST" id="checkoutForm">
  <div class="checkout-grid">

    <!-- Left: Form -->
    <div style="display:flex;flex-direction:column;gap:20px;">

      <!-- Customer Info -->
      <div class="card">
        <div class="card-header"><h3>Customer Information</h3></div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input type="text" name="customer_name" class="form-control"
                     value="<?= htmlspecialchars($_POST['customer_name'] ?? $user['name']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Contact Number *</label>
              <input type="text" name="contact_number" class="form-control"
                     value="<?= htmlspecialchars($_POST['contact_number'] ?? $user['contact_number']) ?>" required
                     placeholder="09XXXXXXXXX">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Delivery Address *</label>
            <textarea name="address" class="form-control" rows="3" required
                      placeholder="House No., Street, Barangay, City, Province"><?= htmlspecialchars($_POST['address'] ?? $user['address'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Order Method -->
      <div class="card">
        <div class="card-header"><h3>Order Method</h3></div>
        <div class="card-body">
          <input type="radio" class="payment-option" name="order_method" id="methodPickup" value="pickup"
                 <?= (($_POST['order_method'] ?? 'pickup') === 'pickup') ? 'checked' : '' ?>>
          <label for="methodPickup" class="payment-option-label">
            <span style="font-size:1.4rem;">🏪</span>
            <span style="font-weight:600;">Store Pickup</span>
          </label>

          <input type="radio" class="payment-option" name="order_method" id="methodShipping" value="shipping"
                 <?= (($_POST['order_method'] ?? '') === 'shipping') ? 'checked' : '' ?>>
          <label for="methodShipping" class="payment-option-label">
            <span style="font-size:1.4rem;">🚚</span>
            <span style="font-weight:600;">Shipping Delivery</span>
          </label>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="card">
        <div class="card-header"><h3>Payment Method</h3></div>
        <div class="card-body">
          <input type="radio" class="payment-option" name="payment_method" id="payCOP" value="cash_on_pickup"
                 <?= (($_POST['payment_method'] ?? 'cash_on_pickup') === 'cash_on_pickup') ? 'checked' : '' ?>>
          <label for="payCOP" class="payment-option-label">
            <span style="font-size:1.4rem;">💵</span>
            <span style="font-weight:600;">Cash on Pickup</span>
          </label>

          <input type="radio" class="payment-option" name="payment_method" id="payCOD" value="cash_on_delivery"
                 <?= (($_POST['payment_method'] ?? '') === 'cash_on_delivery') ? 'checked' : '' ?>>
          <label for="payCOD" id="labelCOD" class="payment-option-label">
            <span style="font-size:1.4rem;">🏠</span>
            <span style="font-weight:600;">Cash on Delivery</span>
          </label>

          <input type="radio" class="payment-option" name="payment_method" id="payGcash" value="gcash"
                 <?= (($_POST['payment_method'] ?? '') === 'gcash') ? 'checked' : '' ?>>
          <label for="payGcash" class="payment-option-label">
            <span style="font-size:1.4rem;">📱</span>
            <span style="font-weight:600;">GCash</span>
          </label>

          <input type="radio" class="payment-option" name="payment_method" id="payBank" value="paymaya"
                 <?= (($_POST['payment_method'] ?? '') === 'paymaya') ? 'checked' : '' ?>>
          <label for="payBank" class="payment-option-label">
            <span style="font-size:1.4rem;">🏦</span>
            <span style="font-weight:600;">PayMaya</span>
          </label>

          <!-- Saved account selector -->
          <div id="accountSelector" style="margin-top:16px;display:none;">
            <?php if (!empty($accounts)): ?>
              <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($accounts as $acc): if (!in_array($acc['account_type'],['gcash','paymaya'])) continue; ?>
                <label class="account-radio-label" data-type="<?= $acc['account_type'] ?>">
                  <input type="radio" name="payment_account_id" value="<?= $acc['account_id'] ?>" <?= $acc['is_default']?'checked':'' ?> style="display:none;">
                  <span style="font-size:1.4rem;"><?= $acc['account_type']==='gcash'?'📱':'🏦' ?></span>
                  <div>
                    <strong><?= htmlspecialchars($acc['account_name']) ?></strong><br>
                    <small><?= $acc['account_type']==='paymaya' && $acc['bank_name']?htmlspecialchars($acc['bank_name']).' • ':'' ?><?= htmlspecialchars($acc['account_number']) ?></small>
                  </div>
                  <?php if ($acc['is_default']): ?><span class="badge badge-amber" style="margin-left:auto;">Default</span><?php endif; ?>
                </label>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:10px;font-size:.85rem;">
                💡 No saved accounts yet. <a href="profile.php?tab=payment" style="color:#2563eb;font-weight:600;">Add one in your profile</a>.
              </div>
              <input type="hidden" name="payment_account_id" value="">
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: Order Summary -->
    <div class="card" style="height:fit-content;position:sticky;top:80px;">
      <div class="card-header"><h3>Order Summary</h3></div>
      <div class="card-body">
        <?php foreach ($cart as $pid => $item): ?>
        <div class="order-item-row">
          <img src="../<?= htmlspecialchars($item['image']) ?>"
               class="order-item-img"
               onerror="this.src='../images/product-placeholder.jpg'"
               alt="<?= htmlspecialchars($item['product_name']) ?>">
          <div class="order-item-info">
            <div class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
            <div class="order-item-qty">Qty: <?= $item['qty'] ?></div>
            <div class="order-item-price">₱<?= number_format($item['price']*$item['qty'],2) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="total-row grand">
          <span>Total</span>
          <span>₱<?= number_format($total,2) ?></span>
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary" style="margin-bottom:8px;">Place Order</button>
        <a href="cart.php" class="btn btn-ghost">← Back to Cart</a>
      </div>
    </div>

  </div>
  </form>
</div>

<script>
const methodPickup   = document.getElementById('methodPickup');
const methodShipping = document.getElementById('methodShipping');
const payCOD         = document.getElementById('payCOD');
const payCOP         = document.getElementById('payCOP');
const payGcash       = document.getElementById('payGcash');
const payBank        = document.getElementById('payBank');
const labelCOD       = document.getElementById('labelCOD');
const accSel         = document.getElementById('accountSelector');

function updatePaymentUI() {
  const isPickup = methodPickup.checked;

  // Disable COD for pickup
  if (labelCOD) {
    labelCOD.style.opacity = isPickup ? '.4' : '1';
    labelCOD.style.pointerEvents = isPickup ? 'none' : '';
    if (isPickup && payCOD.checked) payCOP.checked = true;
  }

  // Show saved accounts if gcash or bank selected
  const payMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
  accSel.style.display = (payMethod === 'gcash' || payMethod === 'paymaya') ? '' : 'none';

  document.querySelectorAll('.account-radio-label').forEach(lbl => {
    const type = lbl.dataset.type;
    lbl.style.display = (payMethod === type) ? '' : 'none';
  });
}

[methodPickup, methodShipping, payCOP, payCOD, payGcash, payBank].forEach(el => {
  if (el) el.addEventListener('change', updatePaymentUI);
});
updatePaymentUI();
</script>
</body>
</html>