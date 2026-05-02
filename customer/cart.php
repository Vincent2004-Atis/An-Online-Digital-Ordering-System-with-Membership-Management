<?php
require_once '../includes/security.php';
require_once '../middleware/auth.php';
requireCustomer();
require_once '../config/database.php';
$db = getDB();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$cart  = $_SESSION['cart'];
$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cart — Amazing World Marketing Corp</title>
<link rel="stylesheet" href="../css/style.css">
<style>
body{font-family:'Plus Jakarta Sans','Sora',sans-serif;margin:0;background:#f9fafb}
.container{max-width:900px;margin:auto;padding:24px 16px}
.card{background:#fff;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.07);margin-bottom:20px;overflow:hidden}
.card-header{padding:16px 20px;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:1rem;color:#0b1f3a;display:flex;justify-content:space-between;align-items:center}
.cart-item{display:flex;align-items:center;gap:16px;padding:16px 20px;border-bottom:1px solid #f1f5f9}
.cart-item:last-child{border-bottom:none}
.item-img{width:72px;height:72px;object-fit:cover;border-radius:10px;flex-shrink:0}
.item-info{flex:1}
.item-name{font-weight:600;color:#0f172a;margin-bottom:4px}
.item-price{color:#2563eb;font-weight:700;font-size:.9rem}
.qty-wrap{display:flex;align-items:center;gap:8px}
.qty-btn{background:#f1f5f9;border:none;width:32px;height:32px;border-radius:8px;font-size:1rem;cursor:pointer;font-weight:700;transition:.2s}
.qty-btn:hover{background:#e2e8f0}
.qty-val{width:40px;text-align:center;font-weight:600;border:1px solid #e2e8f0;border-radius:6px;padding:4px}
.remove-btn{background:none;border:none;color:#ef4444;cursor:pointer;font-size:1.1rem;padding:8px;border-radius:8px;transition:.2s}
.remove-btn:hover{background:#fff1f2}
.summary-row{display:flex;justify-content:space-between;padding:10px 20px;font-size:.9rem;color:#475569}
.summary-total{display:flex;justify-content:space-between;padding:16px 20px;font-weight:800;font-size:1.1rem;color:#0b1f3a;border-top:2px solid #e2e8f0}
.btn{display:block;padding:14px;border-radius:12px;font-weight:700;cursor:pointer;text-align:center;text-decoration:none;font-size:1rem;transition:.2s;border:none;font-family:inherit}
.btn-primary{background:#2563eb;color:#fff}
.btn-primary:hover{background:#1d4ed8}
.btn-outline{background:#fff;color:#2563eb;border:2px solid #2563eb}
.btn-outline:hover{background:#eff6ff}
.empty-cart{text-align:center;padding:60px 24px}
.page-hero{background:linear-gradient(135deg,#0b1f3a,#1a4070);color:#fff;padding:32px 24px;text-align:center}
.page-hero h1{font-size:1.8rem;font-weight:800;margin:0 0 6px}
.page-hero p{opacity:.8;margin:0}
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-hero">
  <h1>🛒 My Cart</h1>
  <p>Review your items before checkout</p>
</div>

<div class="container" style="margin-top:28px">
<?php if (empty($cart)): ?>
  <div class="card empty-cart">
    <div style="font-size:4rem;margin-bottom:16px">🛒</div>
    <h3 style="margin-bottom:8px;color:#0b1f3a">Your cart is empty</h3>
    <p style="color:#64748b;margin-bottom:24px">Add some products to get started!</p>
    <a href="products.php" class="btn btn-primary" style="display:inline-block;width:auto;padding:13px 32px">Browse Products</a>
  </div>
<?php else: ?>
  <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:flex-start">
    <div style="flex:1;min-width:300px">
      <div class="card">
        <div class="card-header">
          <span>Cart Items (<?= count($cart) ?>)</span>
          <button onclick="clearCart()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:.82rem;font-weight:600">Clear All</button>
        </div>
        <?php foreach ($cart as $pid => $item): ?>
        <div class="cart-item" id="row-<?= (int)$pid ?>">
          <img src="../<?= e($item['image'] ?? 'images/product-placeholder.jpg') ?>" class="item-img"
               onerror="this.src='../images/product-placeholder.jpg'" alt="<?= e($item['product_name']) ?>">
          <div class="item-info">
            <div class="item-name"><?= e($item['product_name']) ?></div>
            <div class="item-price">₱<?= number_format($item['price'],2) ?> each</div>
          </div>
          <div class="qty-wrap">
            <button class="qty-btn" onclick="updateQty(<?= (int)$pid ?>, -1)">−</button>
            <input class="qty-val" id="qty-<?= (int)$pid ?>" type="number" value="<?= (int)$item['qty'] ?>" min="1" max="999" onchange="setQty(<?= (int)$pid ?>, this.value)">
            <button class="qty-btn" onclick="updateQty(<?= (int)$pid ?>, 1)">+</button>
          </div>
          <button class="remove-btn" onclick="removeItem(<?= (int)$pid ?>)" title="Remove">🗑️</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="width:300px;flex-shrink:0">
      <div class="card">
        <div class="card-header">Order Summary</div>
        <?php foreach ($cart as $item): ?>
        <div class="summary-row">
          <span><?= e($item['product_name']) ?> ×<?= (int)$item['qty'] ?></span>
          <span>₱<?= number_format($item['price']*$item['qty'],2) ?></span>
        </div>
        <?php endforeach; ?>
        <div class="summary-total">
          <span>Total</span>
          <span id="cartTotal">₱<?= number_format($total,2) ?></span>
        </div>
        <div style="padding:0 16px 16px;display:flex;flex-direction:column;gap:8px">
          <a href="checkout.php" class="btn btn-primary">Proceed to Checkout →</a>
          <a href="products.php" class="btn btn-outline">Continue Shopping</a>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
</div>

<div id="toast-container"></div>
<script>
function updateQty(pid, delta) {
  const input = document.getElementById('qty-'+pid);
  let val = parseInt(input.value) + delta;
  if (val < 1) { removeItem(pid); return; }
  input.value = val;
  setQty(pid, val);
}
function setQty(pid, qty) {
  fetch('/amazingworldmarketingcorp/customer/cart_action.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=update&product_id=${pid}&qty=${qty}`
  }).then(r=>r.json()).then(()=>location.reload());
}
function removeItem(pid) {
  fetch('/amazingworldmarketingcorp/customer/cart_action.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=remove&product_id=${pid}`
  }).then(r=>r.json()).then(()=>location.reload());
}
function clearCart() {
  if (!confirm('Clear all items?')) return;
  fetch('/amazingworldmarketingcorp/customer/cart_action.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=clear'
  }).then(r=>r.json()).then(()=>location.reload());
}
function showToast(msg,type=''){
  const c=document.getElementById('toast-container');
  const t=document.createElement('div');
  t.className='toast '+type; t.textContent=msg; c.appendChild(t);
  setTimeout(()=>t.remove(),3000);
}
</script>
</body>
</html>
