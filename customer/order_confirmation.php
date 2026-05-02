<?php
require_once '../includes/security.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

$db      = getDB();
$userId  = (int)$_SESSION['user_id'];
$orderId = (int)($_GET['order_id'] ?? 0);
if (!$orderId) { header('Location: products.php'); exit; }

// Fetch order
$stmt = $db->prepare("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON u.user_id=o.user_id WHERE o.order_id=? AND o.user_id=?");
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { header('Location: products.php'); exit; }

// Fetch items
$stmt = $db->prepare("SELECT oi.*, p.product_name, p.image FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=?");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$orderMethodIcons = [
    'pickup'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563a8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-9 9 9v10a2 2 0 0 1-2 2h-4v-6h-6v6H5a2 2 0 0 1-2-2z"/></svg> Store Pickup',
    'shipping' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18v12H3z"/><path d="M3 6l9-4 9 4"/></svg> Shipping'
];

$payIcons = [
    'cash_on_pickup'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><line x1="2" y1="7" x2="22" y2="7"/></svg> Cash on Pickup',
    'cash_on_delivery' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563a8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-9 9 9v10a2 2 0 0 1-2 2h-4v-6h-6v6H5a2 2 0 0 1-2-2z"/></svg> Cash on Delivery',
    'gcash'            => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="12" y1="8" x2="12" y2="16"/></svg> GCash',
    'bank_transfer'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a3c5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="12"/><line x1="3" y1="12" x2="21" y2="12"/><polyline points="3 6 12 2 21 6"/></svg> Bank Transfer'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order Confirmed — OrderSync</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body { font-family:'Plus Jakarta Sans','Sora',sans-serif; background:#f9f9f9; margin:0; padding:0; color:#1a1a1a; }
.container { max-width:720px; margin:32px auto; padding:16px; }
.queue-card { background:linear-gradient(135deg,#2563a8,#1a3c5e); border-radius:16px; padding:24px; color:white; text-align:center; margin-bottom:32px; box-shadow:0 8px 20px rgba(0,0,0,0.1);}
.queue-card h2, .queue-card .queue-num { color:white; }
.queue-card p, .queue-card .queue-label { color:rgba(255,255,255,0.9); }
.card { background:white; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.05); margin-bottom:24px; color:#1a1a1a; }
.card-header { padding:16px 20px; border-bottom:1px solid #eee; font-weight:700; font-size:1.1rem; display:flex; align-items:center; gap:8px; color:#1a1a1a; }
.card-body { padding:20px; }
.order-item-row { display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #eee; }
.order-item-img { width:60px; height:60px; object-fit:cover; border-radius:8px; margin-right:12px; }
.order-item-info { flex:1; display:flex; flex-direction:column; }
.order-item-name { font-weight:600; color:#1a1a1a; margin-bottom:4px; }
.order-item-qty { font-size:.85rem; color:#555; }
.order-item-price { font-weight:700; min-width:70px; text-align:right; color:#1a1a1a; }
.total-row { display:flex; justify-content:space-between; padding:12px 0; font-weight:700; font-size:1.1rem; color:#1a1a1a; }
.btn { padding:12px 20px; border-radius:12px; font-weight:600; text-decoration:none; text-align:center; display:inline-block; transition:all 0.2s ease; }
.btn-primary { background:#2563a8; color:white; border:none; }
.btn-primary:hover { background:#1a3c5e; }
.btn-outline { border:2px solid #2563a8; color:#2563a8; }
.btn-outline:hover { background:#2563a8; color:white; }
.queue-num { font-family:'Sora',sans-serif; font-size:5rem; font-weight:800; color:#f59e0b; line-height:1; }
.queue-label { font-size:1rem; opacity:.8; margin-top:8px; }
@media(max-width:600px) { .order-item-row { flex-direction:column; align-items:flex-start; } .order-item-price { text-align:left; margin-top:4px; } }
</style>
</head>
<body>
<?php
require_once '../includes/security.php'; include '../includes/navbar.php'; ?>

<div class="container">

  <div class="queue-card">
    <div style="font-size:3rem;margin-bottom:12px;">✔️</div>
    <h2>Order Confirmed!</h2>
    <p style="opacity:.8;">Your order has been placed successfully.</p>
    <div style="margin:24px 0;">
      <div class="queue-label">Queue Number</div>
      <div class="queue-num"><?= str_pad($order['queue_number'],3,'0',STR_PAD_LEFT) ?></div>
      <div class="queue-label" style="margin-top:8px;">Order #<?= $orderId ?></div>
    </div>
    <?php
require_once '../includes/security.php'; if ($order['order_method'] === 'pickup'): ?>
      <p style="opacity:.8;font-size:.9rem;">Please present this queue number at the store.</p>
    <?php
require_once '../includes/security.php'; else: ?>
      <p style="opacity:.8;font-size:.9rem;">Your order will be delivered to your address.</p>
    <?php
require_once '../includes/security.php'; endif; ?>
  </div>

  <div class="card">
    <div class="card-header">📋 Order Details</div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
        <div><div style="font-size:.75rem;color:#888;font-weight:700;text-transform:uppercase;">Customer</div><div style="font-weight:600;"><?= htmlspecialchars($order['customer_name']) ?></div></div>
        <div><div style="font-size:.75rem;color:#888;font-weight:700;text-transform:uppercase;">Contact</div><div style="font-weight:600;"><?= htmlspecialchars($order['contact_number']) ?></div></div>
        <div><div style="font-size:.75rem;color:#888;font-weight:700;text-transform:uppercase;">Order Method</div><div style="font-weight:600;"><?= $orderMethodIcons[$order['order_method']] ?? $order['order_method'] ?></div></div>
        <div><div style="font-size:.75rem;color:#888;font-weight:700;text-transform:uppercase;">Payment</div><div style="font-weight:600;"><?= $payIcons[$order['payment_method']] ?? $order['payment_method'] ?></div></div>
        <div style="grid-column:1/-1;"><div style="font-size:.75rem;color:#888;font-weight:700;text-transform:uppercase;">Delivery Address</div><div style="font-weight:600;"><?= htmlspecialchars($order['address']) ?></div></div>
      </div>

      <?php
require_once '../includes/security.php'; foreach ($items as $item): ?>
      <div class="order-item-row">
        <img src="../<?= htmlspecialchars($item['image'] ?? 'images/product-placeholder.jpg') ?>"
             class="order-item-img"
             onerror="this.src='../images/product-placeholder.jpg'"
             alt="<?= htmlspecialchars($item['product_name']) ?>">
        <div class="order-item-info">
          <div class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
          <div class="order-item-qty">× <?= $item['quantity'] ?></div>
        </div>
        <div class="order-item-price">₱<?= number_format($item['price']*$item['quantity'],2) ?></div>
      </div>
      <?php
require_once '../includes/security.php'; endforeach; ?>

      <div class="total-row">
        <span>Total Amount</span>
        <span>₱<?= number_format($order['total_amount'],2) ?></span>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:16px;">
    <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
    <a href="products.php" class="btn btn-outline">Continue Shopping</a>
  </div>

</div>
</body>
</html>
