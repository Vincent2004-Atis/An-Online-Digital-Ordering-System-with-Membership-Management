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
<title>Cart — Marguax Collections</title>
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

.container { max-width: 1000px; margin: auto; padding: 28px 24px; }

/* PAGE HERO */
.page-hero {
  background: transparent !important;
  border-bottom: 1px solid rgba(196,80,100,.15) !important;
  color: #f0e6da !important;
  padding: 64px 24px 52px !important;
  text-align: center !important;
  position: relative !important;
}
.page-hero::before {
  content: '';
  position: absolute; inset: 0;
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
.page-hero p {
  color: #7a6058; font-size: .92rem; font-weight: 300; margin: 0;
  animation: heroIn .8s .12s cubic-bezier(.16,1,.3,1) both;
}
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
  margin-bottom: 20px !important;
  animation: fadeUp .55s cubic-bezier(.16,1,.3,1) both;
}
.card-header {
  padding: 18px 22px;
  border-bottom: 1px solid rgba(196,80,100,.12);
  font-family: 'Jost', sans-serif;
  font-weight: 600; font-size: .72rem;
  letter-spacing: .16em; text-transform: uppercase;
  color: #7a6058;
  display: flex; justify-content: space-between; align-items: center;
  background: rgba(14,5,7,.3);
}
.card-header strong { color: #f0e6da; font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 400; text-transform: none; letter-spacing: 0; }

/* CART ITEM */
.cart-item {
  display: flex; align-items: center; gap: 18px;
  padding: 18px 22px;
  border-bottom: 1px solid rgba(196,80,100,.08);
  transition: background .2s;
  animation: fadeUp .5s cubic-bezier(.16,1,.3,1) both;
}
.cart-item:last-child { border-bottom: none; }
.cart-item:hover { background: rgba(196,80,100,.04); }
.item-img {
  width: 76px; height: 76px; object-fit: cover;
  border-radius: 12px; flex-shrink: 0;
  border: 1px solid rgba(196,80,100,.18);
  filter: brightness(.92) saturate(.88);
  transition: filter .3s;
}
.cart-item:hover .item-img { filter: brightness(1) saturate(1); }
.item-info { flex: 1; }
.item-name {
  font-family: 'Playfair Display', serif;
  font-weight: 400; font-size: 1.05rem;
  color: #f0e6da; margin-bottom: 5px;
}
.item-price { color: #c45064; font-size: .82rem; font-weight: 500; letter-spacing: .04em; }

/* QTY */
.qty-wrap { display: flex; align-items: center; gap: 8px; }
.qty-btn {
  background: rgba(196,80,100,.08);
  border: 1px solid rgba(196,80,100,.22);
  color: #c45064;
  width: 34px; height: 34px; border-radius: 8px;
  font-size: 1rem; cursor: pointer; font-weight: 600;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s, transform .15s;
  font-family: 'Jost', sans-serif;
}
.qty-btn:hover { background: #c45064; color: #fff; border-color: #c45064; transform: scale(1.1); }
.qty-val {
  width: 46px; text-align: center;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(196,80,100,.18);
  color: #f0e6da; border-radius: 8px;
  padding: 6px 4px; font-size: .88rem;
  font-family: 'Jost', sans-serif;
}
.qty-val:focus { outline: none; border-color: #c45064; box-shadow: 0 0 0 3px rgba(196,80,100,.12); }

/* REMOVE BTN */
.remove-btn {
  background: none; border: none; cursor: pointer;
  color: rgba(196,80,100,.5); font-size: 1rem;
  padding: 8px; border-radius: 8px;
  transition: color .2s, background .2s;
}
.remove-btn:hover { background: rgba(196,80,100,.1); color: #e8a0a8; }

/* SUMMARY */
.summary-row {
  display: flex; justify-content: space-between;
  padding: 10px 22px; font-size: .85rem;
  color: #7a6058; border-bottom: 1px solid rgba(196,80,100,.06);
}
.summary-row span:last-child { color: #f0e6da; font-weight: 500; }
.summary-total {
  display: flex; justify-content: space-between;
  padding: 18px 22px;
  border-top: 1px solid rgba(196,80,100,.2);
}
.summary-total span:first-child {
  font-family: 'Jost', sans-serif; font-size: .72rem;
  letter-spacing: .18em; text-transform: uppercase; color: #7a6058;
  align-self: center;
}
.summary-total span:last-child {
  font-family: 'Playfair Display', serif;
  font-size: 1.7rem; color: #c45064; font-weight: 400;
}

/* BUTTONS */
.btn {
  display: flex; align-items: center; justify-content: center;
  width: 100%; padding: 13px 18px; border-radius: 10px;
  font-family: 'Jost', sans-serif; font-size: .72rem;
  font-weight: 600; letter-spacing: .14em; text-transform: uppercase;
  cursor: pointer; text-decoration: none; text-align: center;
  transition: all .25s; border: none;
}
.btn-primary { background: #c45064; color: #fff; }
.btn-primary:hover { background: #a83d53; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(196,80,100,.35); }
.btn-outline { background: transparent; color: #7a6058; border: 1px solid rgba(196,80,100,.2); }
.btn-outline:hover { background: rgba(196,80,100,.06); color: #e8a0a8; border-color: rgba(196,80,100,.4); }
.btn-danger { background: none; border: none; color: rgba(196,80,100,.5); font-size: .72rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; cursor: pointer; font-family: 'Jost', sans-serif; transition: color .2s; padding: 0; }
.btn-danger:hover { color: #e8a0a8; }

/* EMPTY CART */
.empty-cart { text-align: center; padding: 80px 24px; }
.empty-icon { font-size: 3rem; margin-bottom: 20px; opacity: .25; }
.empty-cart h3 { font-family: 'Playfair Display', serif; font-size: 1.9rem; font-weight: 400; color: #f0e6da; margin-bottom: 10px; }
.empty-cart p { color: #7a6058; font-weight: 300; margin-bottom: 28px; }

/* TOAST */
#toast-container { position: fixed; bottom: 28px; right: 28px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
.toast {
  background: #2a0d14; border: 1px solid rgba(196,80,100,.3);
  color: #f0e6da; border-radius: 12px; padding: 14px 18px;
  font-family: 'Jost', sans-serif; font-size: .84rem; min-width: 240px;
  animation: toastIn .35s cubic-bezier(.34,1.56,.64,1) both;
  box-shadow: 0 16px 40px rgba(0,0,0,.6);
}

@keyframes heroIn  { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeUp  { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
@keyframes toastIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }

@media(max-width:700px){
  .cart-item { gap: 12px; }
  .item-img { width: 58px; height: 58px; }
}
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="page-hero">
  <div class="hero-eyebrow">Marguax Collections</div>
  <h1>My <em>Cart</em></h1>
  <p>Review your items before checkout</p>
  <div class="hero-divider"></div>
</div>

<div class="container" style="margin-top:32px">
<?php if (empty($cart)): ?>
  <div class="card">
    <div class="empty-cart">
      <div class="empty-icon">◆</div>
      <h3>Your cart is empty</h3>
      <p>Add some products to get started</p>
      <a href="products.php" class="btn btn-primary" style="display:inline-flex;width:auto;padding:13px 40px;">Browse Products</a>
    </div>
  </div>
<?php else: ?>
  <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start">

    <!-- Items -->
    <div style="flex:1;min-width:300px">
      <div class="card">
        <div class="card-header">
          <strong>Cart Items</strong>
          <span style="display:flex;align-items:center;gap:16px">
            <span style="background:rgba(196,80,100,.12);color:#c45064;padding:4px 14px;border-radius:20px;font-size:.68rem;font-weight:600;letter-spacing:.1em"><?= count($cart) ?> item<?= count($cart)!=1?'s':'' ?></span>
            <button onclick="clearCart()" class="btn-danger">Clear All</button>
          </span>
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
          <button class="remove-btn" onclick="removeItem(<?= (int)$pid ?>)" title="Remove">🗑</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Summary -->
    <div style="width:300px;flex-shrink:0">
      <div class="card" style="animation-delay:.15s">
        <div class="card-header"><strong>Order Summary</strong></div>
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
        <div style="padding:0 18px 18px;display:flex;flex-direction:column;gap:10px">
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
  fetch('/Marguax_Collection/customer/cart_action.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=update&product_id=${pid}&qty=${qty}`
  }).then(r=>r.json()).then(()=>location.reload());
}
function removeItem(pid) {
  fetch('/Marguax_Collection/customer/cart_action.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=remove&product_id=${pid}`
  }).then(r=>r.json()).then(()=>location.reload());
}
function clearCart() {
  if (!confirm('Clear all items?')) return;
  fetch('/Marguax_Collection/customer/cart_action.php', {
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
