<?php
// Guard: only accessible from admin pages
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php'); exit;
}
require_once __DIR__ . '/../../config/database.php';
$db = getDB();

// Pending order count for badge
$pendingCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetch_row()[0];

// Unread messages count for badge
$unreadMsgCount = 0;
$msgResult = $db->query("SELECT COUNT(*) FROM messages m JOIN conversations c ON c.conversation_id=m.conversation_id WHERE m.sender_type='customer' AND m.is_read=0");
if ($msgResult) $unreadMsgCount = (int)$msgResult->fetch_row()[0];

$currentFile  = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <!-- Brand -->
  <div class="sidebar-brand">
    <a href="../admin/dashboard.php" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
      <img src="/amazingworldmarketingcorp/images/logo.png"
           alt="Amazing World Logo"
           style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.25);flex-shrink:0;">
      <div>
        <div class="brand-name">Amazing World Marketing Corp.</div>
        <div class="brand-sub">Admin Panel</div>
      </div>
    </a>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Main</div>
    <a href="../admin/dashboard.php"
       class="sidebar-link <?= $currentFile==='dashboard.php'?'active':'' ?>">
      📊 Dashboard
    </a>
    <a href="../admin/analytics.php"
       class="sidebar-link <?= $currentFile==='analytics.php'?'active':'' ?>">
      📈 Analytics
    </a>

    <div class="sidebar-section-label">Management</div>
    <a href="../admin/manage_orders.php"
       class="sidebar-link <?= $currentFile==='manage_orders.php'?'active':'' ?>">
      📋 Orders
      <?php if ($pendingCount > 0): ?>
        <span class="count"><?= $pendingCount ?></span>
      <?php endif; ?>
    </a>
    <a href="../admin/manage_products.php"
       class="sidebar-link <?= $currentFile==='manage_products.php'?'active':'' ?>">
      🛍️ Products
    </a>
    <a href="../admin/manage_users.php"
       class="sidebar-link <?= $currentFile==='manage_users.php'?'active':'' ?>">
      👥 Users
    </a>

    <div class="sidebar-section-label">Support</div>
    <a href="../admin/messages.php"
       class="sidebar-link <?= $currentFile==='messages.php'?'active':'' ?>">
      💬 Messages
      <?php if ($unreadMsgCount > 0): ?>
        <span class="count" style="background:var(--blue);"><?= $unreadMsgCount ?></span>
      <?php endif; ?>
    </a>

    <div class="sidebar-section-label">Account</div>
    <a href="/amazingworldmarketingcorp/auth/logout.php" class="sidebar-link" style="color:rgba(239,68,68,.7);">
      🚪 Logout
    </a>
  </nav>

  <div class="sidebar-footer">
    <div style="font-size:.75rem;color:rgba(255,255,255,.3);padding:0 8px;">
      Logged in as<br>
      <strong style="color:rgba(255,255,255,.6);"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></strong>
    </div>
  </div>
</aside>