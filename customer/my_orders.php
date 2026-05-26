<?php
require_once '../includes/security.php';
if (!isset($_SESSION['user_id'])) { header('Location: /Marguax_Collection/auth/login.php'); exit; }
require_once '../config/database.php';
$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY order_date DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$orderMethodIcons = ['pickup'=>'🏪 Pickup','shipping'=>'🚚 Shipping'];
$payIcons = ['cash_on_pickup'=>'💵 Cash on Pickup','cash_on_delivery'=>'🏠 Cash on Delivery','gcash'=>'📱 GCash','paymaya'=>'🏦 PayMaya','paypal'=>'🌐 PayPal'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order History — Marguax Collections</title>
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

.container { max-width: 1100px; margin: auto; padding: 28px 24px; }

/* HERO */
.page-hero {
  background: transparent !important;
  border-bottom: 1px solid rgba(196,80,100,.15) !important;
  padding: 64px 24px 52px !important;
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
.page-hero h1 {
  font-family: 'Playfair Display', serif !important;
  font-size: clamp(2.2rem, 5vw, 3.8rem) !important;
  font-weight: 700 !important; color: #f0e6da !important;
  line-height: 1.08 !important; margin: 0 0 10px !important;
  animation: heroIn .8s cubic-bezier(.16,1,.3,1) both;
}
.page-hero h1 em { font-style: italic; color: #c45064; }
.page-hero p { color: #7a6058; font-size: .92rem; font-weight: 300; margin: 0; }
.hero-divider {
  width: 56px; height: 1px;
  background: linear-gradient(90deg, transparent, #c45064, transparent);
  margin: 20px auto 0;
}

/* CARD */
.card {
  background: rgba(42,13,20,.7) !important;
  border: 1px solid rgba(196,80,100,.14) !important;
  border-radius: 16px !important;
  overflow: hidden !important;
  box-shadow: none !important;
  animation: fadeUp .55s cubic-bezier(.16,1,.3,1) both;
}
.card-header {
  padding: 18px 22px;
  border-bottom: 1px solid rgba(196,80,100,.12);
  display: flex; justify-content: space-between; align-items: center;
  background: rgba(14,5,7,.3);
}
.card-header-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; font-weight: 400; color: #f0e6da;
}

/* TABLE */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: .875rem; }
thead th {
  text-align: left; padding: 13px 18px;
  border-bottom: 1px solid rgba(196,80,100,.15);
  font-size: .65rem; font-weight: 600; color: #5a4a42;
  text-transform: uppercase; letter-spacing: .1em;
  background: rgba(14,5,7,.25);
}
tbody td {
  padding: 15px 18px;
  border-bottom: 1px solid rgba(196,80,100,.07);
  vertical-align: middle; color: #c8b0a0;
  font-size: .85rem;
}
tbody tr:hover td { background: rgba(196,80,100,.04); }
tbody tr:last-child td { border-bottom: none; }
.order-num { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 400; color: #f0e6da; }
.queue-num { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: #c45064; }
.order-total { font-family: 'Playfair Display', serif; font-size: 1rem; color: #f0e6da; }

/* BADGES */
.badge {
  padding: 5px 13px; border-radius: 20px;
  font-size: .65rem; font-weight: 600; letter-spacing: .08em;
  text-transform: uppercase; display: inline-flex; align-items: center;
}
.badge-pending   { background: rgba(196,80,100,.1);  color: #c45064;  border: 1px solid rgba(196,80,100,.25); }
.badge-processing{ background: rgba(100,130,196,.12); color: #8ab0d4; border: 1px solid rgba(100,130,196,.3); }
.badge-completed { background: rgba(100,196,130,.1);  color: #6dbf8a; border: 1px solid rgba(100,196,130,.25); }
.badge-cancelled { background: rgba(90,74,66,.2);     color: #7a6058; border: 1px solid rgba(90,74,66,.3); }
.badge-paid      { background: rgba(100,196,130,.1);  color: #6dbf8a; border: 1px solid rgba(100,196,130,.25); }
.badge-unpaid    { background: rgba(196,80,100,.1);   color: #c45064; border: 1px solid rgba(196,80,100,.25); }

/* EMPTY */
.order-empty { text-align: center; padding: 80px 24px; }
.order-empty h3 { font-family: 'Playfair Display', serif; font-size: 1.9rem; font-weight: 400; color: #f0e6da; margin-bottom: 10px; }
.order-empty p { color: #7a6058; font-weight: 300; margin-bottom: 28px; }

/* BTN */
.btn-primary {
  display: inline-flex; align-items: center; justify-content: center;
  background: #c45064; color: #fff; border: none;
  border-radius: 10px; padding: 12px 36px;
  font-family: 'Jost', sans-serif; font-size: .72rem;
  font-weight: 600; letter-spacing: .14em; text-transform: uppercase;
  cursor: pointer; text-decoration: none;
  transition: background .25s, transform .2s, box-shadow .25s;
}
.btn-primary:hover { background: #a83d53; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(196,80,100,.35); }

@keyframes heroIn { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }

@media(max-width:700px){
  thead th, tbody td { padding: 10px 12px; }
}
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="page-hero">
  <div class="hero-eyebrow">Marguax Collections</div>
  <h1>Order <em>History</em></h1>
  <p>Track all your past and current orders</p>
  <div class="hero-divider"></div>
</div>

<div class="container" style="margin-top:32px">
  <?php if (empty($orders)): ?>
    <div class="card">
      <div class="order-empty">
        <div style="font-size:2.4rem;margin-bottom:18px;opacity:.2;">◆</div>
        <h3>No orders yet</h3>
        <p>Start shopping to see your orders here</p>
        <a href="products.php" class="btn-primary">Browse Products →</a>
      </div>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="card-header">
        <span class="card-header-title">Order History</span>
        <span style="background:rgba(196,80,100,.12);color:#c45064;padding:5px 16px;border-radius:20px;font-size:.68rem;font-weight:600;letter-spacing:.1em;border:1px solid rgba(196,80,100,.25)"><?= count($orders) ?> order<?= count($orders)!=1?'s':'' ?></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Order #</th><th>Queue</th><th>Date</th>
              <th>Method</th><th>Payment</th><th>Total</th>
              <th>Order Status</th><th>Payment Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o):
              $statusClass = match($o['order_status']) {
                'pending'    => 'badge-pending',
                'processing' => 'badge-processing',
                'completed'  => 'badge-completed',
                'cancelled'  => 'badge-cancelled',
                default      => 'badge-cancelled'
              };
              $payClass = $o['payment_status']==='paid' ? 'badge-paid' : 'badge-unpaid';
            ?>
            <tr>
              <td><span class="order-num">#<?= (int)$o['order_id'] ?></span></td>
              <td><span class="queue-num"><?= str_pad((int)$o['queue_number'],3,'0',STR_PAD_LEFT) ?></span></td>
              <td style="white-space:nowrap"><?= date('M d, Y g:i A', strtotime($o['order_date'])) ?></td>
              <td><?= $orderMethodIcons[$o['order_method']] ?? htmlspecialchars($o['order_method']) ?></td>
              <td><?= $payIcons[$o['payment_method']] ?? htmlspecialchars($o['payment_method']) ?></td>
              <td><span class="order-total">₱<?= number_format((float)$o['total_amount'],2) ?></span></td>
              <td><span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($o['order_status'])) ?></span></td>
              <td><span class="badge <?= $payClass ?>"><?= ucfirst(htmlspecialchars($o['payment_status'])) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
