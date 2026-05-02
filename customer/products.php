<?php
require_once '../includes/security.php';
if (!isset($_SESSION['user_id'])) { header('Location: /amazingworldmarketingcorp/auth/login.php'); exit; }
require_once '../config/database.php';
$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("SELECT name, member_status FROM users WHERE user_id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { session_destroy(); header('Location: /amazingworldmarketingcorp/auth/login.php'); exit; }
$isMember = ($user['member_status'] === 'member');

// Fetch all categories
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$typeFilter     = $_GET['filter'] ?? 'all';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search         = trim($_GET['search'] ?? '');

// Non-members cannot access member-exclusive filter
if (!$isMember && $typeFilter === 'member') {
    header('Location: products.php?filter=loose');
    exit;
}

$where  = ['1=1'];
$params = [];
$types  = '';

if ($typeFilter === 'member' && $isMember) { $where[] = "p.product_type = 'member'"; }
elseif ($typeFilter === 'package') { $where[] = "p.product_type = 'package'"; }
elseif ($typeFilter === 'loose')   { $where[] = "p.product_type = 'loose'"; }

if ($categoryFilter > 0) {
    $where[]  = "p.category_id = ?";
    $types   .= 'i';
    $params[] = $categoryFilter;
}

if (!empty($search)) {
    $where[]  = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $types   .= 'ss';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql  = "SELECT p.*, c.name AS category_name 
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.category_id 
         WHERE " . implode(' AND ', $where) . " ORDER BY p.product_name";
$stmt = $db->prepare($sql);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Product Catalog — Amazing World Marketing Corp</title>
<link rel="stylesheet" href="../css/style.css">
<style>
:root {
  --primary: #2e6ee6;
  --primary-light: #4a9eff;
  --amber: #f0a500;
  --green: #16a34a;
  --border: #3a5080;
  --radius: 14px;
  --shadow: 0 4px 8px rgba(0,0,0,.3);
  --transition: .25s ease;
}
body {
  font-family: 'Plus Jakarta Sans','Sora',sans-serif;
  margin: 0;
  background: #1a2a4a !important;
  color: #ffffff !important;
}
.page-hero {
  background: #243660 !important;
  color: #ffffff !important;
  padding: 16px;
  text-align: center;
  border-bottom: 1px solid #3a5080;
}
.page-hero h1 { font-size: 1.5rem; margin-bottom: 4px; color: #ffffff; }
.page-hero p  { font-size: .875rem; margin: 0; color: #8ab0d4; }
.container { max-width: 100%; margin: auto; padding: 16px 50px; background: #1a2a4a; }

/* ── BANNERS ── */
.member-banner {
  display: flex; align-items: center; gap: 12px;
  padding: 16px 20px; border-radius: var(--radius);
  background: #1e3360 !important;
  border: 1px solid #3a5080 !important;
  transition: all var(--transition);
  margin-bottom: 24px;
}
.member-banner:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,.3) !important; }
.member-banner-icon { font-size: 2rem; }
.member-banner-text h4 { margin: 0; font-size: 1rem; color: #ffffff !important; }
.member-banner-text p  { margin: 2px 0 0; font-size: .875rem; color: #8ab0d4 !important; }

/* ── UPGRADE STRIP ── */
.upgrade-strip {
  background: linear-gradient(135deg, #1e3a6e, #0f2040);
  border: 1px solid #f0a500;
  border-radius: var(--radius);
  padding: 20px 24px;
  display: flex; align-items: center;
  justify-content: space-between;
  gap: 16px; flex-wrap: wrap;
  margin-bottom: 24px;
}
.upgrade-strip-left { display: flex; align-items: center; gap: 14px; }
.upgrade-strip-icon { font-size: 2.4rem; }
.upgrade-strip-text h4 { margin: 0 0 4px; font-size: 1.05rem; color: #fff; font-weight: 700; }
.upgrade-strip-text p  { margin: 0; font-size: .83rem; color: #8ab0d4; }
.upgrade-strip-text p span { color: #f0a500; font-weight: 600; }
.btn-upgrade {
  background: linear-gradient(135deg, #f0a500, #d97706);
  color: #fff !important; border: none; border-radius: 10px;
  padding: 10px 22px; font-weight: 700; font-size: .88rem;
  cursor: pointer; text-decoration: none;
  display: inline-flex; align-items: center; gap: 6px;
  white-space: nowrap; transition: all var(--transition);
  box-shadow: 0 4px 12px rgba(240,165,0,.35);
}
.btn-upgrade:hover {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(240,165,0,.5);
}

/* ── PRODUCT GRID ── */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 10px;
}
.product-card {
  background: #243660 !important;
  border: 1px solid #3a5080 !important;
  border-radius: var(--radius);
  overflow: hidden;
  display: flex; flex-direction: column;
  transition: all var(--transition);
  position: relative;
}
.product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.4) !important; }
.product-card.locked { border-color: rgba(240,165,0,.35) !important; }
.product-card.locked:hover { box-shadow: 0 8px 20px rgba(240,165,0,.12) !important; }

.product-img { position: relative; overflow: hidden; height: 260px; }
.product-img img {
  width: 100%; height: 260px;
  object-fit: cover; object-position: center top;
  display: block; border-bottom: 1px solid #3a5080;
  transition: transform var(--transition);
}
.product-card:hover .product-img img { transform: scale(1.08); }
.product-card.locked .product-img img { filter: blur(4px) brightness(0.5); }

.lock-overlay {
  position: absolute; inset: 0;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 10px; background: rgba(10,20,50,.4); z-index: 2;
}
.lock-icon { font-size: 2.4rem; filter: drop-shadow(0 2px 8px rgba(0,0,0,.7)); }
.lock-label {
  background: rgba(240,165,0,.95); color: #fff;
  font-size: .72rem; font-weight: 800;
  padding: 4px 14px; border-radius: 20px;
  letter-spacing: .05em; text-transform: uppercase;
}

.product-type-badge {
  position: absolute; top: 10px; left: 10px;
  background: var(--primary); color: white;
  font-size: .68rem; font-weight: 700;
  padding: 3px 9px; border-radius: 20px;
  box-shadow: 0 2px 6px rgba(0,0,0,.3); z-index: 3;
}
.product-type-badge.glow { animation: glow 1.8s infinite alternate; }
@keyframes glow {
  0%  { box-shadow: 0 0 6px rgba(255,165,0,.5); }
  100%{ box-shadow: 0 0 12px rgba(255,165,0,1); }
}

.product-info { padding: 12px 14px; display: flex; flex-direction: column; gap: 4px; }
.product-name { font-weight: 600; color: #ffffff !important; font-size: .9rem; }
.product-desc { font-size: .78rem; color: #8ab0d4 !important; height: 36px; overflow: hidden; }
.product-price { font-weight: 700; font-size: .9rem; margin-top: 6px; color: #4a9eff !important; }
.product-card.locked .product-price { filter: blur(5px); user-select: none; pointer-events: none; }
.category-tag { font-size: .7rem; color: #8ab0d4 !important; margin-bottom: 2px; }

/* ── ACTIONS ── */
.product-actions { display: flex; flex-direction: column; gap: 6px; padding: 0 12px 12px; }
.qty-control { display: flex; align-items: center; gap: 6px; }
.qty-btn {
  background: #1a2a4a !important; border: 1px solid #3a5080 !important;
  color: #ffffff !important; padding: 4px 10px; border-radius: 6px;
  cursor: pointer; font-weight: 600;
}
.qty-btn:hover { background: #3a5080 !important; }
.qty-input {
  width: 40px; text-align: center;
  border: 1px solid #3a5080 !important; border-radius: 6px;
  background: #1a2a4a !important; color: #ffffff !important;
}
.btn-full { width: 100%; }
.btn-primary {
  background: #2e6ee6 !important; color: white !important; border: none;
  border-radius: 8px; padding: 8px 12px; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 4px;
  transition: all var(--transition);
}
.btn-primary:hover { background: #4a9eff !important; }
.btn-outline {
  background: #1a2a4a !important; color: #8ab0d4 !important;
  border: 1px solid #3a5080 !important;
  border-radius: 8px; padding: 8px 12px; cursor: not-allowed;
}
.btn-locked {
  background: linear-gradient(135deg, #f0a500, #d97706) !important;
  color: #fff !important; border: none; border-radius: 8px; padding: 8px 12px;
  font-weight: 700; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  transition: all var(--transition); font-size: .85rem; text-decoration: none;
}
.btn-locked:hover {
  background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
  transform: translateY(-1px); box-shadow: 0 4px 12px rgba(240,165,0,.4);
}

/* ── FILTERS ── */
.filter-bar { display: flex; gap: 6px; flex-wrap: wrap; }
.filter-tab {
  padding: 7px 16px; border-radius: 20px; font-size: .82rem; font-weight: 600;
  color: #8ab0d4 !important; background: #243660 !important;
  border: 1px solid #3a5080 !important;
  transition: background 0.50s ease, border-color 0.50s ease, color 0.50s ease, transform 0.25s ease, box-shadow 0.50s ease;
  text-decoration: none; display: inline-block;
  text-transform: uppercase; letter-spacing: 0.04em;
}
.filter-tab:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}
.filter-tab.locked-tab {
  color: #f0a500 !important; border-color: rgba(240,165,0,.4) !important;
  background: rgba(240,165,0,.07) !important;
}
.filter-tab.locked-tab:hover {
  background: rgba(240,165,0,.15) !important; border-color: #f0a500 !important;
}

/* ── EMPTY STATE ── */
.card {
  background: #243660 !important; border: 1px solid #3a5080 !important;
  color: #ffffff !important; border-radius: var(--radius);
  box-shadow: var(--shadow); overflow: hidden;
}
.card h3 { color: #ffffff; }
.card p  { color: #8ab0d4; }

/* ── HOW TO JOIN ── */
.how-to-join {
  background: linear-gradient(135deg, #0d1f3c, #162040);
  border-top: 1px solid #3a5080;
  padding: 60px 50px;
}
.how-to-join h2 {
  text-align: center; font-family: 'Sora', sans-serif;
  font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 8px;
}
.how-to-join .subtitle {
  text-align: center; color: #8ab0d4; font-size: .9rem;
  margin: 0 auto 48px; max-width: 500px; line-height: 1.6;
}
.steps-track {
  position: relative; display: flex;
  justify-content: center; align-items: flex-start;
  max-width: 900px; margin: 0 auto;
}
.steps-track::before {
  content: ''; position: absolute; top: 28px;
  left: calc(16.66% + 28px); right: calc(16.66% + 28px);
  height: 3px; background: linear-gradient(90deg, #f0a500, #2e6ee6); z-index: 0;
}
.step {
  flex: 1; display: flex; flex-direction: column;
  align-items: center; text-align: center;
  position: relative; z-index: 1; padding: 0 16px;
}
.step-number {
  width: 56px; height: 56px; border-radius: 50%;
  background: linear-gradient(135deg, #f0a500, #d97706);
  color: #fff; font-size: 1.4rem; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px; box-shadow: 0 4px 16px rgba(240,165,0,.5);
  font-family: 'Sora', sans-serif;
}
.step-img-placeholder {
  width: 100%; max-width: 240px; height: 150px;
  border-radius: 12px; border: 2px solid #3a5080; margin-bottom: 16px;
  display: flex; align-items: center; justify-content: center; font-size: 3rem;
}
.step:nth-child(1) .step-img-placeholder { background: linear-gradient(135deg,#1e3a6e,#162a56); }
.step:nth-child(2) .step-img-placeholder { background: linear-gradient(135deg,#2a4a20,#1a3012); }
.step:nth-child(3) .step-img-placeholder { background: linear-gradient(135deg,#3a2060,#28104a); }
.step-title { font-size: .95rem; font-weight: 700; color: #fff; margin-bottom: 8px; }
.step-desc  { font-size: .78rem; color: #8ab0d4; line-height: 1.6; }
.join-cta { text-align: center; margin-top: 40px; }
.join-cta p { color: #8ab0d4; font-size: .85rem; margin-bottom: 16px; }

@media(max-width:768px){
  .steps-track { flex-direction: column; align-items: center; gap: 32px; }
  .steps-track::before { display: none; }
  .how-to-join { padding: 40px 20px; }
  .upgrade-strip { flex-direction: column; text-align: center; }
  .upgrade-strip-left { flex-direction: column; text-align: center; }
  footer > div > div:first-of-type { grid-template-columns: 1fr 1fr !important; }
  footer > div > div:first-of-type > div:first-child { grid-column: span 2 !important; }
}
/* ── ANIMATED MEMBER BANNER ── */
.member-banner {
  display: flex; align-items: center; gap: 16px;
  padding: 18px 28px; border-radius: 16px;
  background: linear-gradient(135deg, #1e3a6e 0%, #0f2040 100%) !important;
  border: 1px solid rgba(74, 158, 255, 0.3) !important;
  transition: all 0.4s ease;
  margin-bottom: 28px;
  position: relative; overflow: hidden;
  box-shadow: 0 4px 20px rgba(46, 110, 230, 0.2);
}
.member-banner::before {
  content: '';
  position: absolute; top: 0; left: -100%;
  width: 60%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(74,158,255,0.08), transparent);
  animation: bannerShine 3s infinite;
}
@keyframes bannerShine {
  0%   { left: -100%; }
  100% { left: 200%; }
}
.member-banner:hover {
  transform: translateY(-2px);
  border-color: rgba(74,158,255,0.6) !important;
  box-shadow: 0 8px 30px rgba(46,110,230,0.35) !important;
}
.member-banner-icon { font-size: 2.2rem; animation: float 3s ease-in-out infinite; }
@keyframes float {
  0%,100% { transform: translateY(0); }
  50%      { transform: translateY(-5px); }
}
.member-banner-text h4 { margin: 0; font-size: 1.05rem; color: #ffffff !important; font-weight: 700; }
.member-banner-text p  { margin: 3px 0 0; font-size: .85rem; color: #8ab0d4 !important; }

/* ── FILTER SECTION WRAPPER ── */
.filter-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
}

/* ── FILTER BAR ── */
.filter-bar {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: center;
}

/* ── FILTER TABS ── */
.filter-tab {
  padding: 8px 20px;
  border-radius: 25px;
  font-size: .8rem;
  font-weight: 700;
  color: #8ab0d4 !important;
  background: #1e3060 !important;
  border: 1.5px solid #3a5080 !important;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  position: relative;
  overflow: hidden;
}
.filter-tab::before {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(74,158,255,0.15), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}
.filter-tab:hover {
  color: #ffffff !important;
  border-color: #4a9eff !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(46,110,230,0.35);
}
.filter-tab:hover::before { opacity: 1; }

/* ACTIVE STATE */
.filter-tab.active {
  background: linear-gradient(135deg, #2e6ee6, #1a4ab0) !important;
  color: #ffffff !important;
  border-color: #4a9eff !important;
  box-shadow: 0 4px 16px rgba(46,110,230,0.5);
  transform: translateY(-1px);
}

/* TYPE FILTER TABS (top row) — bigger */
.filter-bar.type-bar .filter-tab {
  padding: 10px 24px;
  font-size: .85rem;
  border-radius: 30px;
}
.filter-bar.type-bar .filter-tab.active {
  background: linear-gradient(135deg, #f0a500, #d97706) !important;
  border-color: #f0a500 !important;
  box-shadow: 0 4px 18px rgba(240,165,0,0.45);
}
.filter-bar.type-bar .filter-tab:hover:not(.active) {
  border-color: #f0a500 !important;
  color: #f0a500 !important;
  box-shadow: 0 4px 14px rgba(240,165,0,0.2);
}

/* LOCKED TAB */
.filter-tab.locked-tab {
  color: #f0a500 !important;
  border-color: rgba(240,165,0,.35) !important;
  background: rgba(240,165,0,.07) !important;
}
.filter-tab.locked-tab:hover {
  background: rgba(240,165,0,.15) !important;
  border-color: #f0a500 !important;
}

/* DIVIDER between type bar and category bar */
.filter-divider {
  width: 100%;
  height: 1px;
  background: linear-gradient(90deg, transparent, #3a5080, transparent);
  margin: 2px 0;
}

/* ── PAGE TRANSITION ── */
.page-transition {
  position: fixed; inset: 0; z-index: 99999;
  pointer-events: none;
  display: flex; align-items: center; justify-content: center;
}
.pt-panel {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #0b1f3a, #2563eb);
  transform: scaleY(0); transform-origin: bottom;
  transition: transform .5s cubic-bezier(.77,0,.18,1);
}
.pt-logo {
  position: relative; z-index: 2;
  opacity: 0; transform: scale(.5);
  transition: all .4s ease .2s; text-align: center;
}
.pt-logo-icon {
  font-size: 3rem; display: block; margin-bottom: 8px;
  animation: ptSpin 1s linear infinite;
}
.pt-logo-text {
  font-family: 'Sora', sans-serif; font-weight: 800;
  font-size: 1rem; color: #fff;
  letter-spacing: .1em; text-transform: uppercase;
}
.pt-logo-bar {
  width: 0; height: 3px;
  background: linear-gradient(90deg, #f59e0b, #f97316);
  border-radius: 2px; margin: 10px auto 0;
  transition: width .5s ease .3s;
}
.page-transition.active .pt-panel  { transform: scaleY(1); }
.page-transition.active .pt-logo   { opacity: 1; transform: scale(1); }
.page-transition.active .pt-logo-bar { width: 120px; }
@keyframes ptSpin {
  0%   { transform: rotate(0deg) scale(1); }
  50%  { transform: rotate(180deg) scale(1.2); }
  100% { transform: rotate(360deg) scale(1); }
}
.ripple-effect {
  position: fixed; border-radius: 50%;
  background: rgba(37,99,235,.25); transform: scale(0);
  animation: rippleOut .6s ease-out forwards;
  pointer-events: none; z-index: 9998;
}
@keyframes rippleOut { to { transform: scale(8); opacity: 0; } }
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<?php
  $heroTitle = 'PRODUCTS';
  $heroDesc  = 'Browse our full range of <span style="color:#f0a500;font-weight:700;">Products</span> from Amazing World Marketing Corporation';
  if ($typeFilter === 'member')  { $heroTitle = 'EXCLUSIVE MEMBER'; $heroDesc = 'Browse exclusive products available only to <span style="color:#f0a500;font-weight:700;">Members</span>'; }
  if ($typeFilter === 'package') { $heroTitle = 'PACKAGES';         $heroDesc = 'Choose a <span style="color:#f0a500;font-weight:700;">Package</span> that fits your lifestyle and budget'; }
?>
<div class="page-hero" style="padding: 40px 50px; text-align: center;">
  <span class="btn-upgrade" style="font-size:1rem;padding:14px 36px;display:inline-flex;margin-bottom:24px;cursor:default;">
    <?= $heroTitle ?>
  </span>
  <?php if (!$isMember && $typeFilter !== 'member' && $typeFilter !== 'package'): ?>
    <p style="color:#8ab0d4;font-size:1rem;margin:0 0 6px;">
      You can only order <span style="color:#f0a500;font-weight:700;">Loose products</span>.
    </p>
    <p style="color:#8ab0d4;font-size:1rem;margin:0;">
      <span style="color:#f0a500;font-weight:700;">Packages</span> and 
      <span style="color:#f0a500;font-weight:700;">Member Exclusives</span> are locked — become a member to unlock them!
    </p>
  <?php else: ?>
    <p style="color:#8ab0d4;font-size:1rem;margin:0;"><?= $heroDesc ?></p>
  <?php endif; ?>
</div>

<div class="container section">

 

 <div class="filter-section">
  <!-- Type Filter (top row) -->
  <div class="filter-bar type-bar">
    <a href="products.php" 
       class="filter-tab <?= ($typeFilter === 'all' || $typeFilter === '') ? 'active' : '' ?>">
       ALL CATEGORIES
    </a>
    <a href="products.php?filter=loose" 
       class="filter-tab <?= $typeFilter === 'loose' ? 'active' : '' ?>">
       LOOSE
    </a>
    <?php if ($isMember): ?>
      <a href="products.php?filter=member"
         class="filter-tab <?= $typeFilter === 'member' ? 'active' : '' ?>">
         MEMBER EXCLUSIVE
      </a>
    <?php else: ?>
      <a href="#" class="filter-tab locked-tab"> MEMBER EXCLUSIVE</a>
    <?php endif; ?>
  </div>

  <div class="filter-divider"></div>

  <!-- Category Filter (bottom row) -->
  <div class="filter-bar">
    <?php foreach ($categories as $cat): ?>
    <a href="products.php?filter=<?= urlencode($typeFilter) ?>&category=<?= $cat['category_id'] ?>"
       class="filter-tab <?= $categoryFilter === $cat['category_id'] ? 'active' : '' ?>">
      <?= htmlspecialchars($cat['name']) ?>
    </a>
    <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($products)): ?>
  <div class="card" style="text-align:center;padding:60px 24px;">
    <div style="font-size:3rem;margin-bottom:16px;">🔍</div>
    <h3>No products found</h3>
    <p>Try adjusting your filters or search terms.</p>
    <a href="products.php" class="btn btn-primary mt-16" style="display:inline-flex;margin-top:16px;">Clear Filters</a>
  </div>
  <?php else: ?>
  <div class="product-grid">
    <?php foreach ($products as $p):
      $typeLabel = match($p['product_type']) {
        'member'  => 'Member Exclusive',
        'package' => 'Package',
        default   => 'Loose'
      };
      $typeClass = match($p['product_type']) {
        'member'  => 'badge-member glow',
        'package' => 'badge-package',
        default   => 'badge-loose'
      };
      $isLocked = !$isMember && $p['product_type'] !== 'loose';
    ?>
    <div class="product-card <?= $isLocked ? 'locked' : '' ?>">
      <div class="product-img">
        <img src="../<?= htmlspecialchars($p['image']) ?>"
             alt="<?= htmlspecialchars($p['product_name']) ?>"
             onerror="this.src='../images/product-placeholder.jpg'">
        <span class="product-type-badge <?= $typeClass ?>"><?= $typeLabel ?></span>
        <?php if (!$isLocked && $p['stock'] <= 10 && $p['stock'] > 0): ?>
          <span class="product-type-badge glow" style="top:10px;right:10px;left:auto;background:rgba(239,68,68,.9);color:#fff;">Low Stock</span>
        <?php elseif (!$isLocked && $p['stock'] == 0): ?>
          <span class="product-type-badge" style="top:10px;right:10px;left:auto;background:rgba(100,116,139,.9);color:#fff;">Out of Stock</span>
        <?php endif; ?>
        <?php if ($isLocked): ?>
        <div class="lock-overlay">
          <div class="lock-icon">🔒</div>
          <div class="lock-label">Members Only</div>
        </div>
        <?php endif; ?>
      </div>

      <div class="product-info">
        <div class="product-name"><?= htmlspecialchars($p['product_name']) ?></div>
        <?php if (!empty($p['category_name'])): ?>
        <div class="category-tag">🏷️ <?= htmlspecialchars($p['category_name']) ?></div>
        <?php endif; ?>
        <div class="product-desc"><?= htmlspecialchars($p['description']) ?></div>
        <div class="product-price">
          <?= $isLocked ? '₱•••••' : '₱' . number_format($p['price'], 2) ?>
        </div>
      </div>

      <?php if ($isLocked): ?>
      <div class="product-actions">
        <a href="#how-to-join" class="btn-locked btn-full">🔒 Unlock — Become a Member</a>
      </div>

      <?php elseif ($p['stock'] > 0): ?>
      <div class="product-actions">
        <div class="qty-control">
          <button class="qty-btn" onclick="changeQty(<?= $p['product_id'] ?>, -1)">−</button>
          <input type="number" class="qty-input" id="qty-<?= $p['product_id'] ?>" value="1" min="1" max="<?= $p['stock'] ?>">
          <button class="qty-btn" onclick="changeQty(<?= $p['product_id'] ?>, 1)">+</button>
          <span style="font-size:.75rem;color:#8ab0d4;">/ <?= $p['stock'] ?> left</span>
        </div>
        <button class="btn btn-primary btn-full"
                onclick="addToCart(<?= $p['product_id'] ?>, '<?= htmlspecialchars(addslashes($p['product_name'])) ?>', <?= $p['price'] ?>)">
          🛒 Add to Cart
        </button>
      </div>

      <?php else: ?>
      <div class="product-actions">
        <button class="btn btn-outline btn-full" disabled>Out of Stock</button>
      </div>
      <?php endif; ?>

    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?php if (!$isMember): ?>
<!-- ── HOW TO BECOME A MEMBER ── -->
<div class="how-to-join" id="how-to-join">
  <h2>3 Simple Steps to Get Started</h2>
  <p class="subtitle">Becoming a member is quick and easy. Follow these three steps and start enjoying exclusive benefits.</p>

  <div class="steps-track">
    <div class="step">
      <div class="step-number">1</div>
      <div class="step-img-placeholder">🎤</div>
      <div class="step-title">Attend ABOP</div>
      <div class="step-desc">Join our Amazing Business Opportunity Presentation (ABOP). Talk to our distributors and learn about the business opportunity.</div>
    </div>
    <div class="step">
      <div class="step-number">2</div>
      <div class="step-img-placeholder">📦</div>
      <div class="step-title">Get Your Package</div>
      <div class="step-desc">Choose the product package that fits your lifestyle and budget. From Silver to Diamond — there's a package for everyone.</div>
    </div>
    <div class="step">
      <div class="step-number">3</div>
      <div class="step-img-placeholder">✅</div>
      <div class="step-title">Sign Up &amp; Create Account</div>
      <div class="step-desc">Register on our ordering system. Get your member account, start ordering products, and enjoy exclusive member discounts.</div>
    </div>
  </div>

  <div class="join-cta">
    <p>Ready to unlock Packages and exclusive member pricing?</p>
    <a href="/amazingworldmarketingcorp/auth/register.php" class="btn-upgrade" style="display:inline-flex;font-size:.95rem;padding:12px 28px;">
       Register &amp; Become a Member
    </a>
  </div>
</div>
<?php endif; ?>

<!-- FOOTER -->
<footer style="background:#060f1c;color:rgba(255,255,255,.6);padding:60px 24px 32px">
  <div style="max-width:1200px;margin:auto">
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:48px">
      <div>
        <img src="../images/logo.png" alt="AWMC" onerror="this.style.display='none'" style="width:52px;height:52px;border-radius:50%;border:2px solid rgba(255,255,255,.2);object-fit:cover;margin-bottom:14px;display:block">
        <p style="font-size:.875rem;line-height:1.7;max-width:280px;color:rgba(255,255,255,.6)">Amazing World Marketing Corporation — bringing premium Ardeur de France products and wellness solutions to Filipino families.</p>
        <div style="display:flex;gap:10px;margin-top:16px">
          <a href="https://facebook.com/amazingworldmktg" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;font-size:1rem;text-decoration:none" onmouseover="this.style.background='rgba(255,255,255,.16)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">📘</a>
          <a href="#" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;font-size:1rem;text-decoration:none" onmouseover="this.style.background='rgba(255,255,255,.16)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">📸</a>
          <a href="#" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;font-size:1rem;text-decoration:none" onmouseover="this.style.background='rgba(255,255,255,.16)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">🐦</a>
        </div>
      </div>
      <div>
        <h4 style="font-family:'Sora',sans-serif;font-weight:700;color:#fff;margin-bottom:16px;font-size:.9rem">Products</h4>
        <a href="products.php?category=male-scents"     style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">MALE SCENTS</a>
        <a href="products.php?category=female-scents"   style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">FEMALE SCENTS</a>
        <a href="products.php?category=health-products" style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">HEALTH</a>
        <a href="products.php?category=boosters"        style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">WELNESS BOOSTERS</a>
        <a href="products.php?category=soaps"           style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">SOAPS &amp; OILS</a>
      </div>
      <div>
        <h4 style="font-family:'Sora',sans-serif;font-weight:700;color:#fff;margin-bottom:16px;font-size:.9rem">Membership</h4>
        <a href="products.php?category=packages&tier=silver"  style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Silver Package</a>
        <a href="products.php?category=packages&tier=gold"    style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Gold Package</a>
        <a href="products.php?category=packages&tier=ruby"    style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Ruby Package</a>
        <a href="products.php?category=packages&tier=emerald" style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Emerald Package</a>
        <a href="products.php?category=packages&tier=diamond" style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Diamond Package</a>
      </div>
      <div>
        <h4 style="font-family:'Sora',sans-serif;font-weight:700;color:#fff;margin-bottom:16px;font-size:.9rem">Company</h4>
        <a href="../index.php#how-to-join" style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">How to Join</a>
        <a href="../index.php#membership"  style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Benefits</a>
        <a href="../auth/login.php"        style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Login</a>
        <a href="../auth/register.php"     style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Register</a>
        <a href="https://www.awmc.io"      style="display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);text-decoration:none" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">www.awmc.io</a>
      </div>
    </div>
    <div style="border-top:1px solid rgba(255,255,255,.08);padding-top:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;font-size:.8rem">
      <span>© 2026 Amazing World Marketing Corporation. All rights reserved.</span>
      <span>🌐 www.awmc.io | 📘 amazingworldmktg</span>
    </div>
  </div>
</footer>

<!-- PAGE TRANSITION OVERLAY -->
<div class="page-transition" id="pageTransition">
  <div class="pt-panel"></div>
  <div class="pt-logo">
    <span class="pt-logo-icon">🌐</span>
    <div class="pt-logo-text">Amazing World</div>
    <div class="pt-logo-bar"></div>
  </div>
</div>

<div id="toast-container"></div>
<script>
function changeQty(id, delta) {
  const input = document.getElementById('qty-' + id);
  let val = parseInt(input.value) + delta;
  val = Math.max(1, Math.min(val, parseInt(input.max)));
  input.value = val;
}
function addToCart(productId, name, price) {
  const qty = parseInt(document.getElementById('qty-' + productId).value) || 1;
  fetch('/amazingworldmarketingcorp/customer/cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${productId}&qty=${qty}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('✅ ' + name + ' added to cart!', 'success');
      const badge = document.querySelector('.cart-badge');
      if (badge) { badge.textContent = data.cart_count; }
    } else {
      showToast('❌ ' + (data.message || 'Failed to add'), 'error');
    }
  })
  .catch(() => showToast('❌ Network error', 'error'));
}
function showToast(msg, type='') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = 'toast ' + type;
  toast.textContent = msg;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ── PAGE TRANSITION ──
const transition = document.getElementById('pageTransition');

function triggerTransition(url) {
  transition.classList.add('active');
  setTimeout(() => { window.location.href = url; }, 700);
}

// Locked MEMBER EXCLUSIVE tab — 1.5s loading transition then instant jump to how-to-join
document.querySelectorAll('a.filter-tab.locked-tab').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    transition.classList.add('active');
    setTimeout(() => {
      transition.classList.remove('active');
      const target = document.getElementById('how-to-join');
      if (target) target.scrollIntoView({ behavior: 'instant', block: 'start' });
    }, 1300);
  });
});

// Ripple on every click
document.addEventListener('click', function(e) {
  const ripple = document.createElement('div');
  const size = 60;
  ripple.className = 'ripple-effect';
  ripple.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - size/2}px;top:${e.clientY - size/2}px;`;
  document.body.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
});

// Remove transition on page load
window.addEventListener('pageshow', () => transition.classList.remove('active'));
</script>

</body>
</html>