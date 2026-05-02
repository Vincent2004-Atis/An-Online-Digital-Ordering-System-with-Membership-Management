<?php
session_start();
// If logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role']==='admin' ? 'admin/dashboard.php' : 'customer/products.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Amazing World Marketing Corporation</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --navy:#0b1f3a;--blue:#2563eb;--amber:#f59e0b;--green:#10b981;
  --teal:#0d9488;--purple:#7c3aed;--red:#ef4444;
  --text:#0f172a;--text-2:#475569;--text-3:#94a3b8;
  --border:#e2e8f0;--bg:#f8fafc;
}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',sans-serif;color:var(--text);background:#fff;overflow-x:hidden}
a{text-decoration:none;color:inherit}
img{max-width:100%}

/* ── NAVBAR ── */
.nav{position:fixed;top:0;left:0;right:0;z-index:1000;transition:all .3s}
.nav.scrolled{background:rgba(11,31,58,.97);backdrop-filter:blur(12px);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.nav-inner{max-width:1200px;margin:auto;padding:0 24px;height:72px;display:flex;align-items:center;justify-content:space-between}
.nav-logo{display:flex;align-items:center;gap:10px}
.nav-logo img{width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,.3);object-fit:cover}
.nav-brand{font-family:'Sora',sans-serif;font-weight:800;font-size:.9rem;color:#fff;line-height:1.1}
.nav-sub{font-size:.6rem;color:var(--amber);font-weight:600;text-transform:uppercase;letter-spacing:.1em}
.nav-links{display:flex;align-items:center;gap:6px}
.nav-link{padding:8px 16px;border-radius:8px;color:rgba(255,255,255,.8);font-size:.875rem;font-weight:600;transition:all .2s}
.nav-link:hover{background:rgba(255,255,255,.12);color:#fff}
.nav-cta{padding:10px 22px;background:var(--amber);color:var(--navy)!important;border-radius:10px;font-weight:700;transition:all .2s!important}
.nav-cta:hover{background:#d97706;transform:translateY(-1px);box-shadow:0 4px 12px rgba(245,158,11,.4)}
.hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:4px}
.hamburger span{width:24px;height:2px;background:#fff;border-radius:2px;transition:.3s}
@media(max-width:768px){
  .nav-links{display:none;position:absolute;top:72px;left:0;right:0;background:rgba(11,31,58,.98);flex-direction:column;padding:20px;gap:4px}
  .nav-links.open{display:flex}
  .hamburger{display:flex}
}

/* ── HERO ── */
.hero{min-height:100vh;background:linear-gradient(135deg,#0b1f3a 0%,#112d52 40%,#1a4070 70%,#0d4a6e 100%);display:flex;align-items:center;position:relative;overflow:hidden;padding-top:72px}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='40' cy='40' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.hero-glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(37,99,235,.15) 0%,transparent 70%);top:-100px;right:-100px;pointer-events:none}
.hero-glow2{position:absolute;width:400px;height:400px;background:radial-gradient(circle,rgba(245,158,11,.1) 0%,transparent 70%);bottom:-50px;left:-50px;pointer-events:none}
.hero-inner{max-width:1200px;margin:auto;padding:80px 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative;z-index:1}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.3);color:var(--amber);padding:8px 16px;border-radius:30px;font-size:.8rem;font-weight:700;margin-bottom:24px;animation:fadeInUp .6s ease}
.hero h1{font-family:'Sora',sans-serif;font-size:3.2rem;font-weight:800;color:#fff;line-height:1.15;margin-bottom:20px;animation:fadeInUp .6s .1s ease both}
.hero h1 span{background:linear-gradient(135deg,#f59e0b,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-desc{color:rgba(255,255,255,.7);font-size:1.05rem;line-height:1.7;margin-bottom:36px;animation:fadeInUp .6s .2s ease both}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap;animation:fadeInUp .6s .3s ease both}
.btn-hero-primary{padding:15px 32px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:12px;font-weight:700;font-size:1rem;transition:all .3s;box-shadow:0 4px 20px rgba(37,99,235,.4)}
.btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(37,99,235,.5)}
.btn-hero-outline{padding:15px 32px;border:2px solid rgba(255,255,255,.3);color:#fff;border-radius:12px;font-weight:700;font-size:1rem;transition:all .3s}
.btn-hero-outline:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.5);transform:translateY(-2px)}
.hero-stats{display:flex;gap:32px;margin-top:40px;animation:fadeInUp .6s .4s ease both}
.hero-stat-val{font-family:'Sora',sans-serif;font-size:1.8rem;font-weight:800;color:#fff}
.hero-stat-lbl{font-size:.78rem;color:rgba(255,255,255,.5);margin-top:2px}
.hero-right{position:relative;animation:fadeInRight .8s .2s ease both}
.hero-card-main{background:rgba(255,255,255,.08);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:0;margin-bottom:16px;overflow:hidden;}
.hero-card-main img{width:100%;height:100%;border-radius:12px;object-fit:cover;object-position:center;transition:transform .4s;display:block;}
.hero-card-mini{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.mini-card{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:16px;text-align:center;transition:all .3s}
.mini-card:hover{background:rgba(255,255,255,.12);transform:translateY(-3px)}
.mini-card .icon{font-size:1.8rem;margin-bottom:8px}
.mini-card .label{font-size:.78rem;color:rgba(255,255,255,.6);font-weight:600}
.mini-card .val{font-size:1.1rem;font-weight:800;color:#fff}
@media(max-width:768px){
  .hero-inner{grid-template-columns:1fr}
  .hero h1{font-size:2.2rem}
  .hero-right{display:none}
  .hero-stats{gap:20px}
}

/* ── SECTIONS COMMON ── */
.section{padding:80px 0}
.section-inner{max-width:1200px;margin:auto;padding:0 24px}
.section-badge{display:inline-block;background:rgba(37,99,235,.1);color:var(--blue);padding:6px 16px;border-radius:20px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px}
.section-title{font-family:'Sora',sans-serif;font-size:2.2rem;font-weight:800;color:var(--navy);margin-bottom:12px}
.section-sub{font-size:1rem;color:var(--text-2);max-width:560px;line-height:1.7}
.text-center{text-align:center}
.text-center .section-sub{margin:0 auto}

/* ── PRODUCTS SECTION ── */
.products-bg{background:var(--bg)}
.products-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;margin-top:48px}
.product-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);transition:all .3s;cursor:pointer;border:1px solid var(--border)}
.product-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,.15);border-color:var(--blue)}
.product-card img{width:100%;height:200px;object-fit:cover;transition:transform .4s}
.product-card:hover img{transform:scale(1.05)}
.product-card-body{padding:20px}
.product-tag{display:inline-block;background:rgba(37,99,235,.1);color:var(--blue);font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px;margin-bottom:8px}
.product-card-body h3{font-family:'Sora',sans-serif;font-size:1rem;font-weight:700;color:var(--navy);margin-bottom:6px}
.product-card-body p{font-size:.82rem;color:var(--text-2);line-height:1.6;margin-bottom:12px}
.product-price{font-family:'Sora',sans-serif;font-size:1.2rem;font-weight:800;color:var(--blue)}

/* ── HOW TO JOIN ── */
.how-bg{background:linear-gradient(135deg,#0b1f3a,#112d52)}
.steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:32px;margin-top:48px;position:relative}
.steps-grid::before{content:'';position:absolute;top:36px;left:calc(16.66% + 20px);right:calc(16.66% + 20px);height:2px;background:linear-gradient(90deg,rgba(245,158,11,.3),rgba(245,158,11,.8),rgba(245,158,11,.3));z-index:0}
.step{text-align:center;position:relative;z-index:1}
.step-num{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--amber),#f97316);color:var(--navy);font-family:'Sora',sans-serif;font-size:1.5rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(245,158,11,.4);transition:all .3s}
.step:hover .step-num{transform:scale(1.1);box-shadow:0 12px 32px rgba(245,158,11,.6)}
.step-img{width:100%;height:160px;object-fit:cover;border-radius:14px;margin-bottom:16px;border:2px solid rgba(255,255,255,.1)}
.step h3{font-family:'Sora',sans-serif;font-weight:700;color:#fff;font-size:1.1rem;margin-bottom:8px}
.step p{color:rgba(255,255,255,.6);font-size:.875rem;line-height:1.6}
@media(max-width:768px){.steps-grid{grid-template-columns:1fr}.steps-grid::before{display:none}}

/* ── PACKAGES ── */
.packages-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;margin-top:48px}
.pkg-card{border-radius:20px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.12);transition:all .3s;position:relative}
.pkg-card:hover{transform:translateY(-6px);box-shadow:0 20px 60px rgba(0,0,0,.2)}
.pkg-card.featured{transform:scale(1.03)}
.pkg-card.featured:hover{transform:scale(1.03) translateY(-6px)}
.pkg-ribbon{position:absolute;top:20px;right:-30px;background:var(--amber);color:var(--navy);font-size:.65rem;font-weight:800;padding:5px 44px;transform:rotate(45deg);z-index:2;letter-spacing:.08em}
.pkg-top{padding:32px 28px;color:#fff;position:relative;overflow:hidden}
.pkg-top::before{content:'';position:absolute;top:-40px;right:-40px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.05)}
.pkg-silver .pkg-top{background:linear-gradient(135deg,#374151,#6b7280)}
.pkg-gold .pkg-top{background:linear-gradient(135deg,#92400e,#d97706)}
.pkg-ruby .pkg-top{background:linear-gradient(135deg,#7f1d1d,#dc2626)}
.pkg-emerald .pkg-top{background:linear-gradient(135deg,#064e3b,#059669)}
.pkg-diamond .pkg-top{background:linear-gradient(135deg,#1e3a5f,#0ea5e9)}
.pkg-icon{font-size:2.5rem;margin-bottom:12px}
.pkg-name{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;margin-bottom:4px}
.pkg-price{font-size:2rem;font-weight:800;line-height:1}
.pkg-price small{font-size:.9rem;font-weight:400;opacity:.7}
.pkg-body{background:#fff;padding:24px 28px}
.pkg-item{display:flex;align-items:flex-start;gap:10px;padding:7px 0;font-size:.875rem;color:var(--text-2);border-bottom:1px solid var(--border)}
.pkg-item:last-of-type{border-bottom:none}
.pkg-item .ck{color:var(--green);font-weight:700;flex-shrink:0;margin-top:1px}
.pkg-roi{background:linear-gradient(135deg,rgba(16,185,129,.1),rgba(37,99,235,.1));border:1px solid rgba(16,185,129,.3);border-radius:10px;padding:12px 16px;margin:16px 0;font-weight:700;color:var(--green);font-size:.9rem}
.pkg-btn{display:block;text-align:center;padding:13px;border-radius:10px;font-weight:700;font-size:.95rem;margin-top:4px;transition:all .3s}
.pkg-silver .pkg-btn{background:linear-gradient(135deg,#374151,#6b7280);color:#fff}
.pkg-gold .pkg-btn{background:linear-gradient(135deg,#92400e,#d97706);color:#fff}
.pkg-ruby .pkg-btn{background:linear-gradient(135deg,#7f1d1d,#dc2626);color:#fff}
.pkg-emerald .pkg-btn{background:linear-gradient(135deg,#064e3b,#059669);color:#fff}
.pkg-diamond .pkg-btn{background:linear-gradient(135deg,#1e3a5f,#0ea5e9);color:#fff}
.pkg-btn:hover{opacity:.9;transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.2)}

/* ── MEMBERSHIP INFO ── */
.membership-bg{background:var(--bg)}
.membership-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;margin-top:48px}
.membership-img{border-radius:20px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.15)}
.membership-img img{width:100%;height:400px;object-fit:cover;display:block;transition:transform .4s}
.membership-img:hover img{transform:scale(1.03)}
.membership-features{display:flex;flex-direction:column;gap:20px}
.mf-item{display:flex;gap:16px;align-items:flex-start;padding:20px;background:#fff;border-radius:14px;border:1px solid var(--border);transition:all .3s}
.mf-item:hover{border-color:var(--blue);box-shadow:0 4px 20px rgba(37,99,235,.1);transform:translateX(6px)}
.mf-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
.mf-icon.blue{background:rgba(37,99,235,.1)}
.mf-icon.amber{background:rgba(245,158,11,.1)}
.mf-icon.green{background:rgba(16,185,129,.1)}
.mf-icon.purple{background:rgba(124,58,237,.1)}
.mf-text h4{font-family:'Sora',sans-serif;font-weight:700;color:var(--navy);margin-bottom:4px}
.mf-text p{font-size:.875rem;color:var(--text-2);line-height:1.6}
@media(max-width:768px){.membership-grid{grid-template-columns:1fr}}

/* ── ADS / PROMOS ── */
.ads-bg{background:linear-gradient(135deg,#0b1f3a,#1a4070)}
.ads-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:40px}
.ad-card{border-radius:16px;overflow:hidden;position:relative;cursor:pointer;transition:all .3s}
.ad-card:hover{transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,.4)}
.ad-card img{width:100%;height:200px;object-fit:cover;display:block;transition:transform .4s}
.ad-card:hover img{transform:scale(1.06)}
.ad-overlay{position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,rgba(0,0,0,.85));display:flex;align-items:flex-end;padding:20px}
.ad-label{font-family:'Sora',sans-serif;font-weight:700;color:#fff;font-size:.95rem}
.ad-sub{font-size:.78rem;color:rgba(255,255,255,.7);margin-top:2px}
.ad-featured{grid-column:span 2}
.ad-featured img{height:300px}
.ad-badge{position:absolute;top:14px;left:14px;background:var(--amber);color:var(--navy);font-size:.7rem;font-weight:800;padding:4px 12px;border-radius:20px}
@media(max-width:768px){.ads-grid{grid-template-columns:1fr}.ad-featured{grid-column:span 1}}

/* ── PAYMENT METHODS ── */
.payment-grid{display:flex;gap:20px;flex-wrap:wrap;justify-content:center;margin-top:40px}
.pay-card{background:#fff;border-radius:16px;padding:24px 32px;display:flex;align-items:center;gap:14px;border:2px solid var(--border);transition:all .3s;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.pay-card:hover{border-color:var(--blue);transform:translateY(-3px);box-shadow:0 8px 24px rgba(37,99,235,.15)}
.pay-icon{font-size:2rem}
.pay-name{font-weight:700;color:var(--navy)}
.pay-desc{font-size:.8rem;color:var(--text-3)}

/* ── TESTIMONIALS ── */
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:48px}
.testi-card{background:#fff;border-radius:16px;padding:28px;border:1px solid var(--border);transition:all .3s;position:relative}
.testi-card:hover{border-color:var(--blue);box-shadow:0 8px 32px rgba(37,99,235,.1);transform:translateY(-4px)}
.testi-card::before{content:'"';position:absolute;top:16px;right:20px;font-size:4rem;color:var(--border);font-family:'Sora',sans-serif;line-height:1}
.testi-stars{color:var(--amber);font-size:1rem;margin-bottom:12px}
.testi-text{font-size:.9rem;color:var(--text-2);line-height:1.7;margin-bottom:16px;font-style:italic}
.testi-author{display:flex;align-items:center;gap:10px}
.testi-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--purple));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.9rem}
.testi-name{font-weight:700;font-size:.875rem;color:var(--navy)}
.testi-role{font-size:.75rem;color:var(--text-3)}
@media(max-width:768px){.testi-grid{grid-template-columns:1fr}}

/* ── CTA BANNER ── */
.cta-section{background:linear-gradient(135deg,#0b1f3a,#2563eb);padding:80px 24px;text-align:center}
.cta-section h2{font-family:'Sora',sans-serif;font-size:2.5rem;font-weight:800;color:#fff;margin-bottom:16px}
.cta-section p{color:rgba(255,255,255,.75);font-size:1.05rem;margin-bottom:36px;max-width:560px;margin-left:auto;margin-right:auto}
.cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.btn-cta-white{padding:15px 36px;background:#fff;color:var(--navy);border-radius:12px;font-weight:800;font-size:1rem;transition:all .3s}
.btn-cta-white:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,255,255,.2)}
.btn-cta-outline{padding:15px 36px;border:2px solid rgba(255,255,255,.4);color:#fff;border-radius:12px;font-weight:700;font-size:1rem;transition:all .3s}
.btn-cta-outline:hover{background:rgba(255,255,255,.1);transform:translateY(-2px)}

/* ── FOOTER ── */
footer{background:#060f1c;color:rgba(255,255,255,.6);padding:60px 24px 32px}
.footer-inner{max-width:1200px;margin:auto}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:48px}
.footer-brand img{width:52px;height:52px;border-radius:50%;border:2px solid rgba(255,255,255,.2);object-fit:cover;margin-bottom:14px}
.footer-brand p{font-size:.875rem;line-height:1.7;max-width:280px}
.footer-col h4{font-family:'Sora',sans-serif;font-weight:700;color:#fff;margin-bottom:16px;font-size:.9rem}
.footer-col a{display:block;font-size:.85rem;margin-bottom:10px;color:rgba(255,255,255,.5);transition:.2s}
.footer-col a:hover{color:#fff}
.footer-bottom{border-top:1px solid rgba(255,255,255,.08);padding-top:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;font-size:.8rem}
.social-links{display:flex;gap:10px}
.social-link{width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;font-size:1rem;transition:.2s}
.social-link:hover{background:rgba(255,255,255,.16)}
@media(max-width:768px){.footer-grid{grid-template-columns:1fr 1fr}.footer-brand{grid-column:span 2}}

/* ── ANIMATIONS ── */
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeInRight{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
.reveal{opacity:0;transform:translateY(30px);transition:all .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="nav" id="navbar">
  <div class="nav-inner">
    <a href="#" class="nav-logo">
      <img src="images/logo.png" alt="AWMC Logo" onerror="this.style.display='none'">
      <div>
        <div class="nav-brand">AMAZING WORLD</div>
        <div class="nav-sub">MARKETING CORPORATION</div>
      </div>
    </a>
    <div class="nav-links" id="navLinks">
      <a href="#products" class="nav-link">PRODUCTS</a>
      <a href="#how-to-join" class="nav-link">HOW TO JOIN</a>
      <a href="#packages" class="nav-link">PACKAGES</a>
      <a href="#membership" class="nav-link">MEMBERSHIP</a>
      <a href="auth/login.php" class="nav-link">LOGIN</a>
      <a href="auth/register.php" class="nav-link nav-cta">GET STARTED</a>
    </div>
    <div class="hamburger" id="hamburger" onclick="toggleNav()">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-glow"></div>
  <div class="hero-glow2"></div>
  <div class="hero-inner">
    <div>
      <div class="hero-badge"> AMAZING WORLD MARKETING CORPORATION</div>
      <h1>Your Success<br>is Our <span>Business</span></h1>
      <p class="hero-desc">Discover premium Ardeur de France fragrances, health supplements, and wellness products. Join our growing community of members and enjoy exclusive benefits and discounts.</p>
      <div class="hero-btns">
        <a href="auth/register.php" class="btn-hero-primary">JOIN NOW</a>
        <a href="#packages" class="btn-hero-outline">VIEW PACKAGES</a>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-card-main">
        <img src="images/products/123.png" alt="Ardeur de France" onerror="this.style.background='rgba(255,255,255,.1)'">
      </div>
      <div class="hero-card-mini">
  <?php
  $isLoggedIn = isset($_SESSION['user_id']);
  $categories = [
    ['label' => 'Female Scents',   'icon' => '', 'slug' => 'female-scents'],
    ['label' => 'Male Scents',     'icon' => '', 'slug' => 'male-scents'],
    ['label' => 'Health Products', 'icon' => '', 'slug' => 'health-products'],
    ['label' => 'Boosters',        'icon' => '', 'slug' => 'boosters'],
  ];
  foreach ($categories as $cat):
    $href = $isLoggedIn
      ? 'customer/products.php?category=' . urlencode($cat['slug'])
      : 'auth/register.php?redirect=' . urlencode('customer/products.php?category=' . $cat['slug']);
  ?>
  <a href="<?= $href ?>" class="mini-card mini-card-link" title="<?= $cat['label'] ?>">
    <div class="icon"><?= $cat['icon'] ?></div>
    <div class="val"><?= $cat['label'] ?></div>
    <?php if (!$isLoggedIn): ?>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section products-bg" id="products">
  <div class="section-inner">
    <div class="text-center reveal">
      <div class="section-badge">Our Products</div>
      <h2 class="section-title">Premium Ardeur de France Collection</h2>
      <p class="section-sub">High-quality fragrances, health supplements, and wellness products crafted for your lifestyle.</p>
    </div>
    <div class="products-grid">
      <div class="product-card reveal">
        <img src="images/products/soap.jpg" alt="Male Scents" onerror="this.style.background='#f1f5f9';this.style.height='200px'">
        <div class="product-card-body">
          <span class="product-tag">Fragrance</span>
          <h3>Male Scents — 16 Variants</h3>
          <p>Inspired by world-famous brands. From M1 CHAMP to SJ BIG BOSS — find your signature scent.</p>
          <div class="product-price">₱498 <small style="font-size:.75rem;color:#94a3b8;font-weight:400">per bottle</small></div>
        </div>
      </div>
      <div class="product-card reveal">
        <img src="images/products/female-scent.jpg" alt="Female Scents" onerror="this.style.background='#f1f5f9';this.style.height='200px'">
        <div class="product-card-body">
          <span class="product-tag">Fragrance</span>
          <h3>Female Scents — 14 Variants</h3>
          <p>From F1 DAZZLING to B BIANCA — elegant and captivating fragrances for every occasion.</p>
          <div class="product-price">₱498 <small style="font-size:.75rem;color:#94a3b8;font-weight:400">per bottle</small></div>
        </div>
      </div>
      <div class="product-card reveal">
        <img src="images/products/immukira.jpg" alt="Health" onerror="this.style.background='#f1f5f9';this.style.height='200px'">
        <div class="product-card-body">
          <span class="product-tag" style="background:rgba(16,185,129,.1);color:#059669">Health</span>
          <h3>Amazing Immu-Pair</h3>
          <p>Immukira-AG & Immuvit-ZinC — boost your immunity with nature's finest ingredients.</p>
          <div class="product-price">₱600 <small style="font-size:.75rem;color:#94a3b8;font-weight:400">per bottle</small></div>
        </div>
      </div>
      <div class="product-card reveal">
        <img src="images/products/boosters.jpg" alt="Boosters" onerror="this.style.background='#f1f5f9';this.style.height='200px'">
        <div class="product-card-body">
          <span class="product-tag" style="background:rgba(124,58,237,.1);color:#7c3aed">Wellness</span>
          <h3>Amazing Healthy Boosters</h3>
          <p>Organic Green Barley, Slimming Coffee, and Extra Strong Coffee for your daily wellness.</p>
          <div class="product-price">₱750 <small style="font-size:.75rem;color:#94a3b8;font-weight:400">per box</small></div>
        </div>
      </div>
    </div>
    <div style="text-align:center;margin-top:40px">
      <a href="auth/register.php" style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:var(--blue);color:#fff;border-radius:12px;font-weight:700;transition:all .3s" onmouseover="this.style.background='#1d4ed8';this.style.transform='translateY(-2px)'" onmouseout="this.style.background='var(--blue)';this.style.transform='none'">
        🛍️ Browse All Products →
      </a>
    </div>
  </div>
</section>

<!-- HOW TO JOIN -->
<section class="section how-bg" id="how-to-join">
  <div class="section-inner">
    <div class="text-center reveal">
      <div class="section-badge" style="background:rgba(245,158,11,.15);color:var(--amber)">How to Join</div>
      <h2 class="section-title" style="color:#fff">3 Simple Steps to Get Started</h2>
      <p class="section-sub" style="color:rgba(255,255,255,.65);margin:0 auto">Becoming a member is quick and easy. Follow these three steps and start enjoying exclusive benefits.</p>
    </div>
    <div class="steps-grid">
      <div class="step reveal">
        <div class="step-num">1</div>
        <img src="images/products/boosters.jpg" alt="Attend ABOP" class="step-img" onerror="this.style.background='rgba(255,255,255,.1)'">
        <h3>Attend ABOP</h3>
        <p>Join our Amazing Business Opportunity Presentation (ABOP). Talk to our distributors and learn more about the business opportunity.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">2</div>
        <img src="images/products/silver-package.jpg" alt="Get Package" class="step-img" onerror="this.style.background='rgba(255,255,255,.1)'">
        <h3>Get Your Package</h3>
        <p>Choose the product package that fits your lifestyle and budget. From Silver to Diamond — there's a package for everyone.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">3</div>
        <img src="images/products/immukira.jpg" alt="Sign Up" class="step-img" onerror="this.style.background='rgba(255,255,255,.1)'">
        <h3>Sign Up & Create Account</h3>
        <p>Register on our ordering system. Get your member account, start ordering products, and enjoy exclusive member discounts.</p>
      </div>
    </div>
  </div>
</section>

<!-- PACKAGES -->
<section class="section" id="packages">
  <div class="section-inner">
    <div class="text-center reveal">
      <div class="section-badge">Business Packages</div>
      <h2 class="section-title">Choose Your Package</h2>
      <p class="section-sub">One-time investment. Predefined sets of products with high ROI. Choose the package that suits your goals.</p>
    </div>
    <div class="packages-grid">
      <div class="pkg-card pkg-silver reveal">
        <div class="pkg-top">
          <div class="pkg-icon">🥈</div>
          <div class="pkg-name">Silver Package</div>
          <div class="pkg-price">₱6,888 <small>one-time</small></div>
        </div>
        <div class="pkg-body">
          <div class="pkg-item"><span class="ck">✓</span>17 Premium Perfumes (Fragrance)</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 14 Immukira-AG / ImmuVit-ZinC</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 11 Boxes + 2 Sachets Boosters</div>
          <div class="pkg-item"><span class="ck">✓</span>Full Member Benefits</div>
          <div class="pkg-roi"> ROI up to ₱8,466</div>
          <a href="auth/register.php" class="pkg-btn">Get Silver Package</a>
        </div>
      </div>
      <div class="pkg-card pkg-gold featured reveal">
        <div class="pkg-ribbon">POPULAR</div>
        <div class="pkg-top">
          <div class="pkg-icon">🥇</div>
          <div class="pkg-name">Gold Package</div>
          <div class="pkg-price">₱10,888 <small>one-time</small></div>
        </div>
        <div class="pkg-body">
          <div class="pkg-item"><span class="ck">✓</span>26 Premium Perfumes (Fragrance)</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 22 Immukira-AG / ImmuVit-ZinC</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 17 Boxes + 5 Sachets Boosters</div>
          <div class="pkg-item"><span class="ck">✓</span>Full Member Benefits + Priority</div>
          <div class="pkg-roi"> ROI up to ₱13,200</div>
          <a href="auth/register.php" class="pkg-btn">Get Gold Package</a>
        </div>
      </div>
      <div class="pkg-card pkg-ruby reveal">
        <div class="pkg-top">
          <div class="pkg-icon">💎</div>
          <div class="pkg-name">Ruby Package</div>
          <div class="pkg-price">₱20,888 <small>one-time</small></div>
        </div>
        <div class="pkg-body">
          <div class="pkg-item"><span class="ck">✓</span>50 Premium Perfumes (Fragrance)</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 42 Immukira-AG / ImmuVit-ZinC</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 33 Boxes + 5 Sachets Boosters</div>
          <div class="pkg-item"><span class="ck">✓</span>All Member Benefits + VIP</div>
          <div class="pkg-roi"> ROI up to ₱25,200</div>
          <a href="auth/register.php" class="pkg-btn">Get Ruby Package</a>
        </div>
      </div>
      <div class="pkg-card pkg-emerald reveal">
        <div class="pkg-top">
          <div class="pkg-icon">🟢</div>
          <div class="pkg-name">Emerald Package</div>
          <div class="pkg-price">₱50,888 <small>one-time</small></div>
        </div>
        <div class="pkg-body">
          <div class="pkg-item"><span class="ck">✓</span>123 Premium Perfumes</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 102 Immukira-AG / ImmuVit-ZinC</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 81 Boxes + 5 Sachets Boosters</div>
          <div class="pkg-item"><span class="ck">✓</span>All Benefits + Dedicated Support</div>
          <div class="pkg-roi"> ROI up to ₱61,254</div>
          <a href="auth/register.php" class="pkg-btn">Get Emerald Package</a>
        </div>
      </div>
      <div class="pkg-card pkg-diamond reveal">
        <div class="pkg-ribbon">ULTIMATE</div>
        <div class="pkg-top">
          <div class="pkg-icon">💠</div>
          <div class="pkg-name">Diamond Package</div>
          <div class="pkg-price">₱100,888 <small>one-time</small></div>
        </div>
        <div class="pkg-body">
          <div class="pkg-item"><span class="ck">✓</span>243 Premium Perfumes</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 202 Immukira-AG / ImmuVit-ZinC</div>
          <div class="pkg-item"><span class="ck">✓</span>OR 161 Boxes + 5 Sachets Boosters</div>
          <div class="pkg-item"><span class="ck">✓</span>All Benefits + VIP Diamond Status</div>
          <div class="pkg-roi"> ROI up to ₱121,200</div>
          <a href="auth/register.php" class="pkg-btn">Get Diamond Package</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MEMBERSHIP BENEFITS -->
<section class="section membership-bg" id="membership">
  <div class="section-inner">
    <div class="reveal">
      <div class="section-badge">Membership Benefits</div>
      <h2 class="section-title">Why Become a Member?</h2>
      <p class="section-sub">Unlock a world of exclusive benefits when you join Amazing World Marketing Corporation as a member.</p>
    </div>
    <div class="membership-grid">
      <div class="membership-img reveal">
        <img src="images/products/gold-package.jpg" alt="Membership" onerror="this.style.background='#f1f5f9'">
      </div>
      <div class="membership-features reveal">
        <div class="mf-item">
          <div class="mf-icon blue">🛍️</div>
          <div class="mf-text">
            <h4>Exclusive Member Products</h4>
            <p>Access a special catalog of member-only products not available to regular customers. Enjoy premium items at exclusive prices.</p>
          </div>
        </div>
        <div class="mf-item">
          <div class="mf-icon amber"></div>
          <div class="mf-text">
            <h4>Member Discounts (5%–15%)</h4>
            <p>Save on every order with tiered discounts. Silver members get 5%, Gold 10%, and higher tiers enjoy up to 15% off.</p>
          </div>
        </div>
        <div class="mf-item">
          <div class="mf-icon green">📦</div>
          <div class="mf-text">
            <h4>High ROI Business Packages</h4>
            <p>Our packages are designed to give you a return on investment. Buy a package, resell products, and earn more than you invested.</p>
          </div>
        </div>
        <div class="mf-item">
          <div class="mf-icon purple">⭐</div>
          <div class="mf-text">
            <h4>Priority Queue & VIP Support</h4>
            <p>Members get priority queue numbers for faster order processing and dedicated customer support for premium tiers.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ADS / PROMOTIONS -->
<section class="section ads-bg">
  <div class="section-inner">
    <div class="text-center reveal">
      <div class="section-badge" style="background:rgba(245,158,11,.15);color:var(--amber)">Promotions</div>
      <h2 class="section-title" style="color:#fff">Featured Products & Promos</h2>
    </div>
    <div class="ads-grid">
      <div class="ad-card ad-featured reveal">
        <div class="ad-badge">🔥 Best Seller</div>
        <img src="images/products/123.png" alt="Male Scents" onerror="this.style.background='rgba(255,255,255,.1)'">
        <div class="ad-overlay">
          <div>
            <div class="ad-label">Ardeur de France — Male Collection</div>
            <div class="ad-sub">16 premium scents inspired by world-famous brands • ₱498 per bottle</div>
          </div>
        </div>
      </div>
      <div class="ad-card reveal">
        <img src="images/products/immukira.jpg" alt="Immukira" onerror="this.style.background='rgba(255,255,255,.1)'">
        <div class="ad-overlay">
          <div>
            <div class="ad-label">Amazing Immu-Pair</div>
            <div class="ad-sub">Nature + Nutrition • ₱600</div>
          </div>
        </div>
      </div>
      <div class="ad-card reveal">
        <img src="images/products/321." alt="Soaps" onerror="this.style.background='rgba(255,255,255,.1)'">
        <div class="ad-overlay">
          <div>
            <div class="ad-label">Ardeur Lightening Soaps</div>
            <div class="ad-sub">Gluta Papaya & Kojic Collagen • ₱180</div>
          </div>
        </div>
      </div>
      <div class="ad-card reveal">
        <img src="images/products/female-scent.jpg" alt="Female Scents" onerror="this.style.background='rgba(255,255,255,.1)'">
        <div class="ad-overlay">
          <div>
            <div class="ad-label">Female Scents Collection</div>
            <div class="ad-sub">14 elegant fragrances • ₱498</div>
          </div>
        </div>
      </div>
      <div class="ad-card reveal">
        <img src="images/products/oil.jpg" alt="Essential Oils" onerror="this.style.background='rgba(255,255,255,.1)'">
        <div class="ad-overlay">
          <div>
            <div class="ad-label">Essential Oils</div>
            <div class="ad-sub">Relaxing & Refreshing • ₱300</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PAYMENT METHODS -->
<section class="section" style="background:var(--bg)">
  <div class="section-inner">
    <div class="text-center reveal">
      <div class="section-badge">Payment Options</div>
      <h2 class="section-title">Flexible Payment Methods</h2>
      <p class="section-sub">We accept multiple payment methods for your convenience.</p>
    </div>
    <div class="payment-grid">
      <div class="pay-card reveal"><span class="pay-icon">💵</span><div><div class="pay-name">Cash on Pickup</div><div class="pay-desc">Pay when you pick up</div></div></div>
      <div class="pay-card reveal"><span class="pay-icon">🏠</span><div><div class="pay-name">Cash on Delivery</div><div class="pay-desc">Pay at your door</div></div></div>
      <div class="pay-card reveal"><span class="pay-icon">📱</span><div><div class="pay-name">GCash</div><div class="pay-desc">Quick mobile payment</div></div></div>
      <div class="pay-card reveal"><span class="pay-icon">🏦</span><div><div class="pay-name">PayMaya</div><div class="pay-desc">Digital wallet payment</div></div></div>
      <div class="pay-card reveal"><span class="pay-icon">🌐</span><div><div class="pay-name">PayPal</div><div class="pay-desc">Secure online payment</div></div></div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section">
  <div class="section-inner">
    <div class="text-center reveal">
      <div class="section-badge">Testimonials</div>
      <h2 class="section-title">What Our Members Say</h2>
    </div>
    <div class="testi-grid">
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">I joined with the Silver Package and already earned back my investment within 2 months! The perfumes are amazing quality and my clients love them.</p>
        <div class="testi-author">
          <div class="testi-avatar">M</div>
          <div><div class="testi-name">Maria Santos</div><div class="testi-role">Silver Member • Quezon City</div></div>
        </div>
      </div>
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">The Immukira-AG has been life-changing for my family. My health improved significantly and I also earn by reselling. Best investment I ever made!</p>
        <div class="testi-author">
          <div class="testi-avatar">J</div>
          <div><div class="testi-name">Jose Reyes</div><div class="testi-role">Gold Member • Cebu City</div></div>
        </div>
      </div>
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">The ordering system is so easy to use. I can place orders anytime, track my queue number, and the delivery is always on time. Highly recommended!</p>
        <div class="testi-author">
          <div class="testi-avatar">A</div>
          <div><div class="testi-name">Ana Cruz</div><div class="testi-role">Ruby Member • Manila</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2>Ready to Start Your Journey?</h2>
  <p>Join thousands of Amazing World Marketing Corporation members and start earning today. No experience needed!</p>
  <div class="cta-btns">
    <a href="auth/register.php" class="btn-cta-white">🚀 Create Free Account</a>
    <a href="#packages" class="btn-cta-outline">📦 View All Packages</a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <img src="images/logo.png" alt="AWMC" onerror="this.style.display='none'">
        <p>Amazing World Marketing Corporation — bringing premium Ardeur de France products and wellness solutions to Filipino families.</p>
        <div class="social-links" style="margin-top:16px">
          <a href="https://facebook.com/amazingworldmktg" class="social-link">📘</a>
          <a href="#" class="social-link">📸</a>
          <a href="#" class="social-link">🐦</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Products</h4>
        <a href="auth/register.php">Male Scents</a>
        <a href="auth/register.php">Female Scents</a>
        <a href="auth/register.php">Health Products</a>
        <a href="auth/register.php">Wellness Boosters</a>
        <a href="auth/register.php">Soaps & Oils</a>
      </div>
      <div class="footer-col">
        <h4>Membership</h4>
        <a href="auth/register.php">Silver Package</a>
        <a href="auth/register.php">Gold Package</a>
        <a href="auth/register.php">Ruby Package</a>
        <a href="auth/register.php">Emerald Package</a>
        <a href="auth/register.php">Diamond Package</a>
      </div>
      <div class="footer-col">
        <h4>Company</h4>
        <a href="#how-to-join">How to Join</a>
        <a href="#membership">Benefits</a>
        <a href="auth/login.php">Login</a>
        <a href="auth/register.php">Register</a>
        <a href="https://www.awmc.io">www.awmc.io</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Amazing World Marketing Corporation. All rights reserved.</span>
      <span>🌐 www.awmc.io | 📘 amazingworldmktg</span>
    </div>
  </div>
</footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
});

// Mobile nav
function toggleNav() {
  document.getElementById('navLinks').classList.toggle('open');
}

// Smooth scroll for nav links
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    document.getElementById('navLinks').classList.remove('open');
  });
});

// Scroll reveal animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>








<!-- PAGE TRANSITION -->
<style>
.page-transition{position:fixed;inset:0;z-index:99999;pointer-events:none;display:flex;align-items:center;justify-content:center}
.pt-panel{position:absolute;inset:0;background:linear-gradient(135deg,#0b1f3a,#2563eb);transform:scaleY(0);transform-origin:bottom;transition:transform .5s cubic-bezier(.77,0,.18,1)}
.pt-logo{position:relative;z-index:2;opacity:0;transform:scale(.5);transition:all .4s ease .2s;text-align:center}
.pt-logo-icon{font-size:3rem;display:block;margin-bottom:8px;animation:ptSpin 1s linear infinite}
.pt-logo-text{font-family:'Sora',sans-serif;font-weight:800;font-size:1rem;color:#fff;letter-spacing:.1em;text-transform:uppercase}
.pt-logo-bar{width:0;height:3px;background:linear-gradient(90deg,#f59e0b,#f97316);border-radius:2px;margin:10px auto 0;transition:width .5s ease .3s}
.page-transition.active .pt-panel{transform:scaleY(1)}
.page-transition.active .pt-logo{opacity:1;transform:scale(1)}
.page-transition.active .pt-logo-bar{width:120px}
@keyframes ptSpin{0%{transform:rotate(0deg) scale(1)}50%{transform:rotate(180deg) scale(1.2)}100%{transform:rotate(360deg) scale(1)}}
.ripple-effect{position:fixed;border-radius:50%;background:rgba(37,99,235,.25);transform:scale(0);animation:rippleOut .6s ease-out forwards;pointer-events:none;z-index:9998}
@keyframes rippleOut{to{transform:scale(8);opacity:0}}
</style>

<div class="page-transition" id="pageTransition">
  <div class="pt-panel"></div>
  <div class="pt-logo">
    <span class="pt-logo-icon">🌐</span>
    <div class="pt-logo-text">Amazing World</div>
    <div class="pt-logo-bar"></div>
  </div>
</div>

<script>
const transition = document.getElementById('pageTransition');

function triggerTransition(url) {
  transition.classList.add('active');
  setTimeout(() => { window.location.href = url; }, 1300);
}

// Intercept all internal links (skip anchor #links)
document.querySelectorAll('a[href]').forEach(link => {
  const href = link.getAttribute('href');
  if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('mailto')) return;
  link.addEventListener('click', function(e) {
    e.preventDefault();
    triggerTransition(this.href);
  });
});

// Ripple effect on every click
document.addEventListener('click', function(e) {
  const ripple = document.createElement('div');
  const size = 60;
  ripple.className = 'ripple-effect';
  ripple.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - size/2}px;top:${e.clientY - size/2}px;`;
  document.body.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
});

// Remove transition on page load
window.addEventListener('pageshow', () => {
  transition.classList.remove('active');
});
</script>

<div class="page-transition" id="pageTransition">
  <div class="pt-panel"></div>
  <div class="pt-logo">
    <span class="pt-logo-icon">🌐</span>
    <div class="pt-logo-text">Amazing World</div>
    <div class="pt-logo-bar"></div>
  </div>
  
</div>
</body>
</html>