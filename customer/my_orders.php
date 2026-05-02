<?php
require_once '../includes/security.php';
if (!isset($_SESSION['user_id'])) { header('Location: /amazingworldmarketingcorp/auth/login.php'); exit; }
require_once '../config/database.php';
$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY order_date DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$statusBadge = ['pending'=>'badge-amber','processing'=>'badge-blue','completed'=>'badge-green','cancelled'=>'badge-gray'];
$orderMethodIcons = ['pickup'=>'🏪 Pickup','shipping'=>'🚚 Shipping'];
$payIcons = ['cash_on_pickup'=>'💵 Cash on Pickup','cash_on_delivery'=>'🏠 Cash on Delivery','gcash'=>'📱 GCash','paymaya'=>'🏦 PayMaya','paypal'=>'🌐 PayPal'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order History — Amazing World Marketing Corp</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body{font-family:'Plus Jakarta Sans','Sora',sans-serif;margin:0;background:#f8fafc;}
.container{max-width:1100px;margin:auto;padding:24px 16px;}
.card{border:1px solid #e2e8f0;border-radius:12px;background:white;margin-bottom:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.card-header{padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;background:#fafafa;}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:.875rem;}
thead th{text-align:left;padding:12px 16px;border-bottom:2px solid #e2e8f0;font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;background:#f8fafc;}
tbody td{padding:13px 16px;border-bottom:1px solid #e2e8f0;vertical-align:middle;}
tbody tr:hover td{background:#f8fafc;}
tbody tr:last-child td{border-bottom:none;}
.badge{padding:5px 12px;border-radius:20px;font-size:.72rem;font-weight:700;display:inline-flex;align-items:center;}
.badge-amber{background:#fef3c7;color:#92400e;}
.badge-blue{background:#dbeafe;color:#1e40af;}
.badge-green{background:#dcfce7;color:#166534;}
.badge-gray{background:#f1f5f9;color:#475569;}
.order-empty{text-align:center;padding:80px 24px;}
.btn{padding:10px 24px;border-radius:10px;font-weight:700;cursor:pointer;transition:all .2s;display:inline-block;text-decoration:none;text-align:center;}
.btn-primary{background:#1a3c5e;color:white;border:none;}
.btn-primary:hover{background:#2563a8;}
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="page-hero">
  <div class="page-hero-inner">
    <h1> ORDER HISTORY</h1>
     <p style="color:#8ab0d4;font-size:1rem;margin:0;">
    Track all your <span style="color:#f0a500;font-weight:700;">past and current orders</span>
  </p>
  </div>
</div>

<div class="container" style="margin-top:24px">
  <?php if (empty($orders)): ?>
    <div class="card order-empty">
      <div style="font-size:4rem;margin-bottom:16px">📦</div>
      <h3 style="margin-bottom:8px;color:#0b1f3a">No orders yet</h3>
      <p style="color:#64748b;margin-bottom:24px">Start shopping to see your orders here.</p>
      <a href="products.php" class="btn btn-primary">Browse Products →</a>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="card-header">
        <h3 style="font-family:'Sora',sans-serif;font-size:1rem;font-weight:800;color:#0b1f3a">Order History</h3>
        <span class="badge badge-blue"><?= count($orders) ?> order<?= count($orders)!=1?'s':'' ?></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Order #</th><th>Queue</th><th>Date</th><th>Method</th>
              <th>Payment</th><th>Total</th><th>Order Status</th><th>Payment Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
              <td><strong style="color:#0b1f3a">#<?= (int)$o['order_id'] ?></strong></td>
              <td><span style="font-family:'Sora',sans-serif;font-weight:800;font-size:1rem;color:#f59e0b"><?= str_pad((int)$o['queue_number'],3,'0',STR_PAD_LEFT) ?></span></td>
              <td style="color:#64748b;white-space:nowrap"><?= date('M d, Y g:i A', strtotime($o['order_date'])) ?></td>
              <td><?= $orderMethodIcons[$o['order_method']] ?? htmlspecialchars($o['order_method']) ?></td>
              <td><?= $payIcons[$o['payment_method']] ?? htmlspecialchars($o['payment_method']) ?></td>
              <td><strong style="color:#0b1f3a">₱<?= number_format((float)$o['total_amount'],2) ?></strong></td>
              <td><span class="badge <?= $statusBadge[$o['order_status']]??'badge-gray' ?>"><?= ucfirst(htmlspecialchars($o['order_status'])) ?></span></td>
              <td><span class="badge <?= $o['payment_status']==='paid'?'badge-green':'badge-amber' ?>"><?= ucfirst(htmlspecialchars($o['payment_status'])) ?></span></td>
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