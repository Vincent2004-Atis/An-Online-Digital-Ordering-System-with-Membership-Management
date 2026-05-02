<?php
require_once '../includes/security.php';
require_once '../middleware/auth.php';
requireAdmin();
require_once '../config/database.php';
$db = getDB();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Amazing World Admin</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/admin.css">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.stats-grid-main { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
.charts-row      { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; }
.chart-card      { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 2px 10px rgba(0,0,0,.07); }
.chart-card h4   { font-family:'Sora',sans-serif; font-size:.9rem; font-weight:700; color:#0b1f3a; margin-bottom:16px; }
.recent-table-wrap { overflow-x:auto; }
.loading-row td  { text-align:center; padding:40px; color:#94a3b8; font-size:.9rem; }
.quick-actions   { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:24px; }

/* SVG Chart styles */
.svg-chart-wrap  { width:100%; overflow:hidden; }
.svg-chart-wrap svg { width:100%; display:block; }

/* Tooltip */
#chartTooltip {
  position:fixed; background:#0b1f3a; color:#fff;
  padding:7px 12px; border-radius:8px; font-size:.78rem;
  font-family:'Plus Jakarta Sans',sans-serif; pointer-events:none;
  opacity:0; transition:opacity .15s; z-index:9999; white-space:nowrap;
}

/* Doughnut legend */
.donut-legend { display:flex; flex-direction:column; gap:7px; margin-top:14px; }
.donut-legend-item { display:flex; align-items:center; gap:8px; font-size:.8rem; color:#374151; }
.donut-legend-dot  { width:11px; height:11px; border-radius:50%; flex-shrink:0; }

/* Empty state */
.chart-empty { text-align:center; padding:50px 20px; color:#94a3b8; font-size:.85rem; }
</style>
</head>
<body>
<div class="admin-layout">
  <?php include 'includes/sidebar.php'; ?>
  <div class="admin-content">

    <div class="admin-topbar">
      <span class="admin-topbar-title">📊 Dashboard</span>
      <div class="admin-topbar-actions">
        <select id="daysSelect" class="form-control" style="width:auto;padding:6px 12px;font-size:.82rem;" onchange="loadAll()">
          <option value="7">Last 7 days</option>
          <option value="14">Last 14 days</option>
          <option value="30" selected>Last 30 days</option>
          <option value="90">Last 90 days</option>
        </select>
        <button class="btn btn-outline btn-sm" onclick="loadAll()">🔄 Refresh</button>
      </div>
    </div>

    <div class="admin-page">

      <!-- Stats Grid -->
      <div class="stats-grid-main" id="statsGrid">
        <?php for($i=0;$i<6;$i++): ?>
        <div class="stat-card"><div class="stat-icon stat-icon-blue">…</div><div><div class="stat-val">—</div><div class="stat-lbl">Loading…</div></div></div>
        <?php endfor; ?>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <a href="manage_orders.php"   class="btn btn-primary">📋 Manage Orders</a>
        <a href="manage_products.php" class="btn btn-outline">🛍️ Manage Products</a>
        <a href="manage_users.php"    class="btn btn-outline">👥 Manage Users</a>
        <a href="messages.php"        class="btn btn-outline">💬 Messages</a>
        <a href="analytics.php"       class="btn btn-outline">📈 Analytics</a>
      </div>

      <!-- Charts Row -->
      <div class="charts-row">
        <div class="chart-card">
          <h4>📈 Revenue (Last <span id="chartDaysLabel">30</span> days)</h4>
          <div class="svg-chart-wrap" id="revenueChartWrap">
            <div class="chart-empty">Loading…</div>
          </div>
        </div>
        <div class="chart-card">
          <h4>🥧 Order Status</h4>
          <div id="statusChartWrap">
            <div class="chart-empty">Loading…</div>
          </div>
        </div>
      </div>

      <!-- Recent Orders + Top Products -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
        <div class="chart-card">
          <h4>🕐 Recent Orders</h4>
          <div class="recent-table-wrap">
            <table id="recentTable">
              <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
              <tbody><tr class="loading-row"><td colspan="5">Loading…</td></tr></tbody>
            </table>
          </div>
        </div>
        <div class="chart-card">
          <h4>🏆 Top Products</h4>
          <div id="topProducts"><div style="text-align:center;padding:30px;color:#94a3b8;font-size:.85rem;">Loading…</div></div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Tooltip -->
<div id="chartTooltip"></div>

<script>
// ─── SVG Line Chart (Revenue) ────────────────────────────────────────────────
function renderRevenueChart(rows) {
  const wrap = document.getElementById('revenueChartWrap');

  if (!rows || !rows.length) {
    wrap.innerHTML = '<div class="chart-empty">No revenue data for this period.</div>';
    return;
  }

  const W = 600, H = 200;
  const padL = 54, padR = 16, padT = 16, padB = 36;
  const chartW = W - padL - padR;
  const chartH = H - padT - padB;

  const values  = rows.map(r => parseFloat(r.revenue));
  const labels  = rows.map(r => {
    const d = new Date(r.day);
    return (d.getMonth()+1) + '/' + d.getDate();
  });
  const maxVal  = Math.max(...values, 1);
  const minVal  = 0;
  const range   = maxVal - minVal || 1;

  // Y-axis grid lines & labels (5 steps)
  const steps = 5;
  let gridLines = '', yLabels = '';
  for (let i = 0; i <= steps; i++) {
    const v  = minVal + (range * i / steps);
    const cy = padT + chartH - (chartH * i / steps);
    gridLines += `<line x1="${padL}" y1="${cy}" x2="${W - padR}" y2="${cy}"
      stroke="#f1f5f9" stroke-width="1"/>`;
    yLabels += `<text x="${padL - 6}" y="${cy + 4}" text-anchor="end"
      font-size="9" fill="#94a3b8">₱${v >= 1000 ? (v/1000).toFixed(1)+'k' : v.toFixed(0)}</text>`;
  }

  // Points
  const pts = rows.map((r, i) => {
    const x = padL + (chartW * i / Math.max(rows.length - 1, 1));
    const y = padT + chartH - (chartH * (parseFloat(r.revenue) - minVal) / range);
    return { x, y, label: labels[i], val: parseFloat(r.revenue) };
  });

  // Polyline path
  const linePath = pts.map((p, i) => (i === 0 ? `M${p.x},${p.y}` : `L${p.x},${p.y}`)).join(' ');

  // Fill path (close to bottom)
  const fillPath = linePath
    + ` L${pts[pts.length-1].x},${padT + chartH} L${padL},${padT + chartH} Z`;

  // X-axis labels (show max ~7 to avoid clutter)
  const step = Math.ceil(rows.length / 7);
  let xLabels = '';
  pts.forEach((p, i) => {
    if (i % step === 0 || i === pts.length - 1) {
      xLabels += `<text x="${p.x}" y="${H - 4}" text-anchor="middle"
        font-size="9" fill="#94a3b8">${p.label}</text>`;
    }
  });

  // Hover circles (invisible, large hit area)
  let circles = '', hitAreas = '';
  pts.forEach((p, i) => {
    circles += `<circle cx="${p.x}" cy="${p.y}" r="4" fill="#2563eb" stroke="#fff" stroke-width="2"
      class="rev-dot" data-val="${p.val}" data-label="${p.label}" data-idx="${i}"/>`;
    hitAreas += `<rect x="${p.x - 14}" y="${padT}" width="28" height="${chartH}"
      fill="transparent" class="rev-hit" data-val="${p.val}" data-label="${p.label}"/>`;
  });

  wrap.innerHTML = `
    <svg viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="revGrad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#2563eb" stop-opacity="0.15"/>
          <stop offset="100%" stop-color="#2563eb" stop-opacity="0.01"/>
        </linearGradient>
      </defs>
      ${gridLines}
      ${yLabels}
      <path d="${fillPath}" fill="url(#revGrad)"/>
      <path d="${linePath}" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"/>
      ${circles}
      ${xLabels}
      ${hitAreas}
    </svg>`;

  // Tooltip on hover
  const tooltip = document.getElementById('chartTooltip');
  wrap.querySelectorAll('.rev-hit').forEach(el => {
    el.addEventListener('mousemove', e => {
      tooltip.textContent = `${el.dataset.label}  ₱${fmt(el.dataset.val)}`;
      tooltip.style.opacity = '1';
      tooltip.style.left = (e.clientX + 12) + 'px';
      tooltip.style.top  = (e.clientY - 28) + 'px';
    });
    el.addEventListener('mouseleave', () => { tooltip.style.opacity = '0'; });
  });
}

// ─── SVG Doughnut Chart (Order Status) ──────────────────────────────────────
function renderStatusChart(dist) {
  const wrap = document.getElementById('statusChartWrap');

  if (!dist || !dist.length) {
    wrap.innerHTML = '<div class="chart-empty">No order data yet.</div>';
    return;
  }

  const colors = { pending:'#f59e0b', processing:'#3b82f6', completed:'#10b981', cancelled:'#94a3b8' };
  const colorList = ['#f59e0b','#3b82f6','#10b981','#94a3b8','#e11d48'];

  const total  = dist.reduce((s, d) => s + parseInt(d.count), 0);
  const cx = 80, cy = 80, R = 68, r = 40;
  const W = 180, H = 170;

  let slices = '';
  let angle  = -Math.PI / 2; // start at top

  dist.forEach((d, i) => {
    const count  = parseInt(d.count);
    const frac   = count / total;
    const sweep  = frac * 2 * Math.PI;
    const endA   = angle + sweep;
    const color  = colors[d.order_status] || colorList[i % colorList.length];

    const x1 = cx + R * Math.cos(angle);
    const y1 = cy + R * Math.sin(angle);
    const x2 = cx + R * Math.cos(endA);
    const y2 = cy + R * Math.sin(endA);
    const xi1 = cx + r * Math.cos(angle);
    const yi1 = cy + r * Math.sin(angle);
    const xi2 = cx + r * Math.cos(endA);
    const yi2 = cy + r * Math.sin(endA);
    const large = sweep > Math.PI ? 1 : 0;

    slices += `<path d="M${xi1},${yi1} L${x1},${y1} A${R},${R} 0 ${large},1 ${x2},${y2} L${xi2},${yi2} A${r},${r} 0 ${large},0 ${xi1},${yi1} Z"
      fill="${color}" stroke="#fff" stroke-width="2">
      <title>${ucfirst(d.order_status)}: ${count}</title>
    </path>`;
    angle = endA;
  });

  // Center label
  const centerLabel = `<text x="${cx}" y="${cy - 5}" text-anchor="middle" font-size="20" font-weight="700" fill="#0b1f3a">${total}</text>
    <text x="${cx}" y="${cy + 13}" text-anchor="middle" font-size="9" fill="#94a3b8">Total Orders</text>`;

  const svg = `<svg viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">
    ${slices}
    ${centerLabel}
  </svg>`;

  // Legend
  const legend = dist.map((d, i) => {
    const color = colors[d.order_status] || colorList[i % colorList.length];
    const pct   = ((parseInt(d.count) / total) * 100).toFixed(0);
    return `<div class="donut-legend-item">
      <div class="donut-legend-dot" style="background:${color}"></div>
      <span>${ucfirst(d.order_status)}</span>
      <span style="margin-left:auto;font-weight:600;color:#0b1f3a;">${d.count} <span style="color:#94a3b8;font-weight:400;">(${pct}%)</span></span>
    </div>`;
  }).join('');

  wrap.innerHTML = `<div style="display:flex;flex-direction:column;align-items:center;">${svg}</div>
    <div class="donut-legend">${legend}</div>`;
}

// ─── Stats Grid ──────────────────────────────────────────────────────────────
function renderStats(s) {
  const grid = document.getElementById('statsGrid');
  const items = [
    { icon:'💰', val:'₱'+fmt(s.total_revenue), lbl:'Total Revenue',  cls:'stat-icon-green' },
    { icon:'📋', val:s.total_orders,            lbl:'Total Orders',   cls:'stat-icon-blue'  },
    { icon:'⏳', val:s.pending_orders,           lbl:'Pending Orders', cls:'stat-icon-amber' },
    { icon:'✅', val:s.completed_orders,         lbl:'Completed',      cls:'stat-icon-green' },
    { icon:'👥', val:s.total_customers,          lbl:'Customers',      cls:'stat-icon-blue'  },
    { icon:'⭐', val:s.total_members,            lbl:'Members',        cls:'stat-icon-amber' },
  ];
  grid.innerHTML = items.map(i => `
    <div class="stat-card">
      <div class="stat-icon ${i.cls}">${i.icon}</div>
      <div><div class="stat-val">${i.val}</div><div class="stat-lbl">${i.lbl}</div></div>
    </div>`).join('');
}

// ─── Recent Orders ───────────────────────────────────────────────────────────
function renderRecentOrders(orders) {
  const statusBadge = { pending:'badge-amber', processing:'badge-blue', completed:'badge-green' };
  const tbody = document.querySelector('#recentTable tbody');
  if (!orders || !orders.length) {
    tbody.innerHTML = '<tr class="loading-row"><td colspan="5">No orders yet.</td></tr>';
    return;
  }
  tbody.innerHTML = orders.map(o => `
    <tr>
      <td><strong>#${o.order_id}</strong><br><span style="color:#f59e0b;font-size:.78rem;font-weight:700;">Q${String(o.queue_number).padStart(3,'0')}</span></td>
      <td>${esc(o.customer_name)}</td>
      <td><strong>₱${fmt(o.total_amount)}</strong></td>
      <td><span class="badge ${statusBadge[o.order_status]||'badge-gray'}">${ucfirst(o.order_status)}</span></td>
      <td style="font-size:.75rem;color:#94a3b8;">${fmtDate(o.order_date)}</td>
    </tr>`).join('');
}

// ─── Top Products ────────────────────────────────────────────────────────────
function renderTopProducts(products) {
  const el = document.getElementById('topProducts');
  if (!products || !products.length) {
    el.innerHTML = '<div style="text-align:center;padding:30px;color:#94a3b8;font-size:.85rem;">No sales data yet.</div>';
    return;
  }
  const max = Math.max(...products.map(p => parseInt(p.units_sold)));
  el.innerHTML = products.map((p, i) => `
    <div style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px;">
        <span style="font-weight:600;color:#0b1f3a;">${i+1}. ${esc(p.product_name)}</span>
        <span style="color:#64748b;">${p.units_sold} sold</span>
      </div>
      <div style="height:6px;background:#f1f5f9;border-radius:3px;">
        <div style="height:6px;background:#2563eb;border-radius:3px;width:${Math.round(parseInt(p.units_sold)/max*100)}%;transition:width .4s;"></div>
      </div>
    </div>`).join('');
}

// ─── Main Loader ─────────────────────────────────────────────────────────────
async function loadAll() {
  const days = document.getElementById('daysSelect').value;
  document.getElementById('chartDaysLabel').textContent = days;
  try {
    const res  = await fetch(`/amazingworldmarketingcorp/api/dashboard.php?days=${days}`);
    const data = await res.json();
    if (!data.success) return;
    renderStats(data.stats);
    renderRevenueChart(data.revenue);
    renderStatusChart(data.status_dist);
    renderRecentOrders(data.recent_orders);
    renderTopProducts(data.top_products);
  } catch(e) { console.error('Dashboard load error:', e); }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function fmt(n)      { return parseFloat(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function ucfirst(s)  { return s ? s.charAt(0).toUpperCase()+s.slice(1) : ''; }
function esc(s)      { const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
function fmtDate(ds) { return ds ? new Date(ds).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}) : '—'; }

loadAll();
</script>
</body>
</html>