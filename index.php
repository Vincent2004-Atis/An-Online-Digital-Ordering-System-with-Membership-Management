<?php
session_start();
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
<title>Marguax Collections — Fashion Boutique</title>
<link href="https://fonts.googleapis.com/css2?family=Didact+Gothic&family=Bodoni+Moda:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --noir:#0e0b0d;
  --noir2:#1c1318;
  --rose:#d4647a;
  --dusty:#c9a0a8;
  --blush:#f5dde2;
  --cream:#faf5f0;
  --gold:#c8a96a;
  --gold2:#e8c87a;
  --ivory:#fef9f4;
  --text-dim:rgba(255,255,255,.5);
  --text-mid:rgba(255,255,255,.75);
  --border-d:rgba(196,160,168,.18);
}
html{scroll-behavior:smooth}
body{font-family:'Jost',sans-serif;background:var(--ivory);color:var(--noir);overflow-x:hidden;cursor:none}
a{text-decoration:none;color:inherit}
img{max-width:100%;display:block}

/* ── CURSOR ── */
#cur-dot{position:fixed;width:8px;height:8px;background:var(--rose);border-radius:50%;pointer-events:none;z-index:99999;transform:translate(-50%,-50%);transition:transform .08s}
#cur-ring{position:fixed;width:38px;height:38px;border:1px solid rgba(212,100,122,.55);border-radius:50%;pointer-events:none;z-index:99998;transform:translate(-50%,-50%);transition:width .25s,height .25s,border-color .25s}
body:hover #cur-dot{opacity:1}

/* ── PETALS ── */
.petals{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.petal{position:absolute;border-radius:60% 30% 70% 20%/50% 40% 60% 50%;opacity:0;animation:fall linear infinite}
@keyframes fall{0%{opacity:0;transform:translateY(-20px) rotate(0deg) scale(1)}8%{opacity:.55}88%{opacity:.25}100%{opacity:0;transform:translateY(105vh) rotate(400deg) scale(.7)}}

/* ── NAV ── */
.nav{position:fixed;top:0;left:0;right:0;z-index:1000;transition:all .4s}
.nav.scrolled{background:rgba(14,11,13,.94);backdrop-filter:blur(20px);border-bottom:1px solid var(--border-d)}
.nav-inner{max-width:1360px;margin:auto;padding:0 56px;height:76px;display:flex;align-items:center;justify-content:space-between}

/* LOGO */
.nav-logo{display:flex;align-items:center;gap:14px;flex-shrink:0}
.logo-svg-wrap{flex-shrink:0}
.logo-monogram{width:48px;height:48px}
.logo-text-block{display:flex;flex-direction:column}
.logo-name{font-family:'Bodoni Moda',serif;font-size:1.1rem;font-weight:700;color:#fff;letter-spacing:.08em;line-height:1.1}
.logo-name em{font-style:italic;color:var(--rose)}
.logo-tagline{font-size:.58rem;letter-spacing:.22em;color:var(--gold);text-transform:uppercase;margin-top:2px}

.nav-links{display:flex;align-items:center;gap:6px}
.nav-link{padding:8px 16px;color:rgba(255,255,255,.7);font-size:.78rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;transition:color .25s;position:relative}
.nav-link::after{content:'';position:absolute;bottom:4px;left:50%;transform:translateX(-50%);width:0;height:1px;background:var(--rose);transition:width .3s}
.nav-link:hover{color:#fff}
.nav-link:hover::after{width:50%}
.nav-cta{padding:10px 26px!important;background:linear-gradient(135deg,var(--rose),#b84060);color:#fff!important;border-radius:50px;letter-spacing:.12em!important;font-weight:600;box-shadow:0 4px 20px rgba(212,100,122,.4);transition:all .3s!important}
.nav-cta::after{display:none!important}
.nav-cta:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(212,100,122,.55)!important}
.hamburger{display:none;flex-direction:column;gap:5px;cursor:none;padding:4px}
.hamburger span{width:22px;height:1px;background:rgba(255,255,255,.8);transition:.3s}
@media(max-width:960px){
  .nav-inner{padding:0 24px}
  .nav-links{display:none;position:absolute;top:76px;left:0;right:0;background:rgba(14,11,13,.97);backdrop-filter:blur(20px);flex-direction:column;padding:28px 32px;gap:8px;border-bottom:1px solid var(--border-d)}
  .nav-links.open{display:flex}
  .hamburger{display:flex}
}

/* ── HERO ── */
.hero{min-height:100vh;background:linear-gradient(150deg,#0e0b0d 0%,#1c1318 45%,#261520 80%,#0e0b0d 100%);display:flex;align-items:center;position:relative;overflow:hidden;padding-top:76px}
.hero-grain{position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.035'/%3E%3C/svg%3E");pointer-events:none}
.hero-blob{position:absolute;border-radius:50%;pointer-events:none;animation:breathe 8s ease-in-out infinite}
.hb1{width:700px;height:700px;background:radial-gradient(circle,rgba(212,100,122,.1) 0%,transparent 70%);top:-180px;right:-180px}
.hb2{width:480px;height:480px;background:radial-gradient(circle,rgba(200,169,106,.07) 0%,transparent 70%);bottom:-120px;left:-120px;animation-delay:3s}
.hb3{width:280px;height:280px;background:radial-gradient(circle,rgba(212,100,122,.08) 0%,transparent 70%);top:50%;left:42%;transform:translate(-50%,-50%);animation-delay:1.5s}
@keyframes breathe{0%,100%{transform:scale(1)}50%{transform:scale(1.14)}}

.hero-inner{max-width:1360px;margin:auto;padding:90px 56px;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;position:relative;z-index:1}
@media(max-width:960px){.hero-inner{grid-template-columns:1fr;padding:60px 24px;gap:48px}}

/* Hero left */
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(212,100,122,.12);border:1px solid rgba(212,100,122,.3);color:rgba(212,100,122,.9);padding:8px 20px;border-radius:50px;font-size:.7rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;margin-bottom:30px;animation:fadeUp .7s ease both}
.hero-badge::before{content:'✦'}
.hero h1{font-family:'Bodoni Moda',serif;font-size:clamp(3rem,5.2vw,5.5rem);font-weight:900;color:#fff;line-height:1.05;margin-bottom:24px;animation:fadeUp .7s .1s ease both}
.hero h1 .line-script{display:block;font-style:italic;background:linear-gradient(135deg,var(--gold2),var(--gold),var(--rose));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-desc{font-family:'Jost',sans-serif;color:rgba(255,255,255,.6);font-size:1rem;line-height:1.85;margin-bottom:40px;max-width:480px;font-weight:300;animation:fadeUp .7s .2s ease both}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap;animation:fadeUp .7s .3s ease both}
.btn-hero-rose{display:inline-flex;align-items:center;gap:10px;padding:16px 36px;background:linear-gradient(135deg,var(--rose),#b84060);color:#fff;border-radius:50px;font-size:.82rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;box-shadow:0 6px 28px rgba(212,100,122,.4);transition:all .35s;position:relative;overflow:hidden}
.btn-hero-rose::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);transition:left .5s}
.btn-hero-rose:hover::before{left:100%}
.btn-hero-rose:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(212,100,122,.55)}
.btn-hero-ghost{display:inline-flex;align-items:center;gap:10px;padding:16px 34px;border:1px solid rgba(255,255,255,.22);color:rgba(255,255,255,.8);border-radius:50px;font-size:.82rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;transition:all .35s}
.btn-hero-ghost:hover{background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.45);transform:translateY(-3px)}
.hero-stats{display:flex;gap:40px;margin-top:46px;animation:fadeUp .7s .42s ease both}
.stat-val{font-family:'Bodoni Moda',serif;font-size:2.1rem;font-weight:700;background:linear-gradient(135deg,var(--gold2),var(--rose));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.stat-lbl{font-size:.68rem;color:rgba(255,255,255,.4);letter-spacing:.14em;text-transform:uppercase;margin-top:2px}

/* Hero right — category cards */
.hero-right{animation:fadeInR .9s .2s ease both}
@keyframes fadeInR{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
.hero-cards-top{background:rgba(255,255,255,.05);border:1px solid rgba(212,100,122,.18);border-radius:24px;overflow:hidden;margin-bottom:14px;position:relative}
.hero-cards-top img{width:100%;height:290px;object-fit:cover;transition:transform .5s}
.hero-cards-top:hover img{transform:scale(1.04)}
.hero-img-overlay{position:absolute;inset:0;background:linear-gradient(180deg,transparent 50%,rgba(14,11,13,.8));display:flex;align-items:flex-end;padding:22px}
.hero-img-label{font-family:'Bodoni Moda',serif;font-size:1rem;font-weight:700;color:#fff;font-style:italic}
.hero-img-sub{font-size:.72rem;color:rgba(255,255,255,.6);letter-spacing:.08em;margin-top:3px}
.hero-mini-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.mini-cat{background:rgba(255,255,255,.05);border:1px solid rgba(212,100,122,.14);border-radius:18px;padding:20px 16px;text-align:center;transition:all .38s;cursor:none}
.mini-cat:hover{background:rgba(212,100,122,.14);border-color:rgba(212,100,122,.4);transform:translateY(-5px)}
.mini-cat-icon{font-size:2rem;margin-bottom:10px;display:block;animation:floatIcon 4.5s ease-in-out infinite}
.mini-cat:nth-child(2) .mini-cat-icon{animation-delay:1s}
.mini-cat:nth-child(3) .mini-cat-icon{animation-delay:2s}
.mini-cat:nth-child(4) .mini-cat-icon{animation-delay:3s}
@keyframes floatIcon{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.mini-cat-label{font-size:.8rem;color:rgba(255,255,255,.82);font-weight:500;letter-spacing:.05em}
.mini-cat-sub{font-size:.68rem;color:rgba(255,255,255,.38);margin-top:3px}
@keyframes fadeUp{from{opacity:0;transform:translateY(26px)}to{opacity:1;transform:translateY(0)}}

/* ── RIBBON ── */
.ribbon{background:linear-gradient(135deg,var(--rose),#b84060,var(--rose));padding:13px 0;overflow:hidden;position:relative;z-index:1}
.ribbon-track{display:flex;white-space:nowrap;animation:ribbonScroll 26s linear infinite}
.ribbon-item{display:inline-flex;align-items:center;gap:22px;color:rgba(255,255,255,.85);font-size:.68rem;letter-spacing:.22em;text-transform:uppercase;font-weight:500;padding-right:52px}
.ribbon-sep{color:rgba(255,255,255,.3)}
@keyframes ribbonScroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ── SECTION COMMON ── */
.sec{padding:96px 0}
.sec-inner{max-width:1360px;margin:auto;padding:0 56px}
@media(max-width:768px){.sec-inner{padding:0 24px}.sec{padding:64px 0}}
.eyebrow{font-size:.68rem;letter-spacing:.24em;text-transform:uppercase;color:var(--rose);font-weight:600;margin-bottom:12px}
.eyebrow::before{content:'✦  '}
.sec-title{font-family:'Bodoni Moda',serif;font-size:clamp(2rem,3.2vw,3rem);font-weight:900;line-height:1.18;margin-bottom:12px}
.sec-title em{font-style:italic;color:var(--rose)}
.sec-sub{font-size:.95rem;color:#7a5a62;line-height:1.8;max-width:520px;font-weight:300}
.t-center{text-align:center}.t-center .sec-sub{margin:0 auto}
.reveal{opacity:0;transform:translateY(32px);transition:opacity .8s ease,transform .8s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* ── SHOP CATEGORIES (main) ── */
.shop-cats{background:var(--cream)}
.cats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-top:52px}
@media(max-width:900px){.cats-grid{grid-template-columns:1fr 1fr}}
.cat-card{border-radius:22px;overflow:hidden;position:relative;cursor:none;aspect-ratio:3/4;transition:all .42s cubic-bezier(.23,1,.32,1)}
.cat-card img{width:100%;height:100%;object-fit:cover;transition:transform .6s}
.cat-card:hover{transform:translateY(-8px);box-shadow:0 24px 60px rgba(212,100,122,.2)}
.cat-card:hover img{transform:scale(1.07)}
.cat-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(14,11,13,0) 35%,rgba(14,11,13,.85));display:flex;flex-direction:column;justify-content:flex-end;padding:24px 20px}
.cat-pill{display:inline-block;background:rgba(212,100,122,.9);color:#fff;font-size:.62rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:8px;width:fit-content}
.cat-name{font-family:'Bodoni Moda',serif;font-size:1.25rem;font-weight:700;color:#fff;font-style:italic}
.cat-count{font-size:.74rem;color:rgba(255,255,255,.55);margin-top:4px;letter-spacing:.06em}
.cat-arrow{position:absolute;top:18px;right:18px;width:36px;height:36px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem;opacity:0;transform:translateY(-6px);transition:all .35s}
.cat-card:hover .cat-arrow{opacity:1;transform:translateY(0)}

/* ── BRAND NEW SECTION ── */
.brandnew-bg{background:#fff}
.products-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:22px;margin-top:52px}
.prod-card{border-radius:20px;overflow:hidden;border:1px solid rgba(212,100,122,.1);background:#fff;transition:all .42s cubic-bezier(.23,1,.32,1);cursor:none;position:relative}
.prod-card:hover{transform:translateY(-8px);box-shadow:0 20px 52px rgba(212,100,122,.14);border-color:rgba(212,100,122,.35)}
.prod-img-wrap{position:relative;overflow:hidden}
.prod-card img{width:100%;height:300px;object-fit:cover;transition:transform .55s}
.prod-card:hover img{transform:scale(1.06)}
.prod-badge{position:absolute;top:14px;left:14px;padding:5px 14px;border-radius:20px;font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase}
.badge-new{background:linear-gradient(135deg,var(--rose),#b84060);color:#fff}
.badge-preowned{background:linear-gradient(135deg,var(--gold),#a07830);color:#fff}
.badge-sale{background:linear-gradient(135deg,#e8547a,#c2185b);color:#fff}
.prod-wish{position:absolute;top:14px;right:14px;width:36px;height:36px;background:rgba(255,255,255,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.95rem;opacity:0;transform:scale(.8);transition:all .3s}
.prod-card:hover .prod-wish{opacity:1;transform:scale(1)}
.prod-body{padding:20px 18px}
.prod-tag{font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;color:var(--rose);font-weight:600;margin-bottom:6px}
.prod-name{font-family:'Bodoni Moda',serif;font-size:1.05rem;font-weight:700;color:var(--noir);margin-bottom:6px;font-style:italic}
.prod-desc{font-size:.8rem;color:#7a5a62;line-height:1.65;margin-bottom:14px}
.prod-foot{display:flex;align-items:center;justify-content:space-between}
.prod-price{font-family:'Bodoni Moda',serif;font-size:1.2rem;font-weight:700;color:var(--rose)}
.prod-price .was{font-size:.78rem;color:#bbb;text-decoration:line-through;font-family:'Jost',sans-serif;font-weight:400;margin-left:6px}
.prod-btn{padding:8px 18px;background:linear-gradient(135deg,var(--rose),#b84060);color:#fff;border-radius:50px;font-size:.72rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;transition:all .3s}
.prod-btn:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(212,100,122,.4)}

/* ── PRE-OWNED FEATURE ── */
.preowned-bg{background:linear-gradient(150deg,var(--noir) 0%,var(--noir2) 50%,#261520 100%);position:relative;overflow:hidden}
.preowned-bg::before{content:'PREOWNED';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-family:'Bodoni Moda',serif;font-size:12rem;font-weight:900;color:rgba(255,255,255,.02);white-space:nowrap;pointer-events:none;letter-spacing:.1em}
.preowned-inner{display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center}
@media(max-width:900px){.preowned-inner{grid-template-columns:1fr}}
.preowned-imgs{display:grid;grid-template-columns:1fr 1fr;gap:14px;position:relative}
.po-img-main{grid-column:1/-1;border-radius:22px;overflow:hidden;border:1px solid rgba(212,100,122,.2)}
.po-img-main img{width:100%;height:280px;object-fit:cover;transition:transform .5s}
.po-img-main:hover img{transform:scale(1.04)}
.po-img-sm{border-radius:18px;overflow:hidden;border:1px solid rgba(212,100,122,.15)}
.po-img-sm img{width:100%;height:180px;object-fit:cover;transition:transform .5s}
.po-img-sm:hover img{transform:scale(1.06)}
.po-float-tag{position:absolute;top:-16px;right:-16px;background:linear-gradient(135deg,var(--gold),#a07830);color:#fff;padding:10px 20px;border-radius:50px;font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;box-shadow:0 6px 20px rgba(200,169,106,.4);z-index:2}
.preowned-content .eyebrow{color:var(--gold)}
.preowned-content .sec-title{color:#fff}
.preowned-content .sec-sub{color:rgba(255,255,255,.55)}
.po-perks{display:flex;flex-direction:column;gap:16px;margin-top:32px}
.po-perk{display:flex;align-items:center;gap:16px;padding:18px 20px;background:rgba(255,255,255,.04);border:1px solid rgba(212,100,122,.12);border-radius:14px;transition:all .35s}
.po-perk:hover{background:rgba(212,100,122,.1);border-color:rgba(212,100,122,.3);transform:translateX(6px)}
.po-perk-icon{width:44px;height:44px;background:linear-gradient(135deg,rgba(212,100,122,.2),rgba(200,169,106,.1));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.po-perk-title{font-family:'Bodoni Moda',serif;font-weight:700;color:#fff;font-size:.95rem;margin-bottom:3px}
.po-perk-desc{font-size:.78rem;color:rgba(255,255,255,.45);line-height:1.6}
.po-btns{display:flex;gap:14px;margin-top:36px;flex-wrap:wrap}

/* ── HOW TO ORDER ── */
.howorder-bg{background:var(--cream)}
.steps-row{display:grid;grid-template-columns:repeat(4,1fr);gap:28px;margin-top:52px;position:relative}
.steps-row::before{content:'';position:absolute;top:44px;left:calc(12.5% + 14px);right:calc(12.5% + 14px);height:1px;background:linear-gradient(90deg,transparent,rgba(212,100,122,.3) 20%,rgba(212,100,122,.7) 50%,rgba(212,100,122,.3) 80%,transparent)}
@media(max-width:900px){.steps-row{grid-template-columns:1fr 1fr}.steps-row::before{display:none}}
.step-card{text-align:center;position:relative;z-index:1}
.step-num{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--rose),#b84060);color:#fff;font-family:'Bodoni Moda',serif;font-size:1.7rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 28px rgba(212,100,122,.4);transition:all .35s}
.step-card:hover .step-num{transform:scale(1.12);box-shadow:0 14px 40px rgba(212,100,122,.6)}
.step-icon{font-size:1.6rem;margin-bottom:12px;display:block}
.step-title{font-family:'Bodoni Moda',serif;font-weight:700;font-size:1rem;color:var(--noir);margin-bottom:8px;font-style:italic}
.step-desc{font-size:.8rem;color:#7a5a62;line-height:1.7}


/* ── PAYMENT ── */
.pay-grid{display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin-top:44px}
.pay-card{background:#fff;border-radius:16px;padding:20px 28px;display:flex;align-items:center;gap:14px;border:1px solid rgba(212,100,122,.15);transition:all .35s;box-shadow:0 2px 10px rgba(0,0,0,.04)}
.pay-card:hover{border-color:rgba(212,100,122,.45);transform:translateY(-4px);box-shadow:0 10px 28px rgba(212,100,122,.12)}
.pay-icon{font-size:1.9rem}
.pay-name{font-weight:600;color:var(--noir);font-size:.88rem}
.pay-desc{font-size:.74rem;color:#b07a8a}

/* ── CTA BANNER ── */
.cta-section{background:linear-gradient(150deg,var(--noir) 0%,#261520 40%,var(--rose) 100%);padding:96px 56px;text-align:center;position:relative;overflow:hidden}
.cta-section::before{content:'M';position:absolute;top:-60px;left:40px;font-family:'Bodoni Moda',serif;font-size:24rem;font-weight:900;color:rgba(255,255,255,.02);line-height:1;pointer-events:none}
.cta-section h2{font-family:'Bodoni Moda',serif;font-size:clamp(2.2rem,4vw,3.4rem);font-weight:900;color:#fff;margin-bottom:16px}
.cta-section h2 em{font-style:italic;color:rgba(255,255,255,.65)}
.cta-section p{color:rgba(255,255,255,.65);font-size:1rem;margin-bottom:40px;max-width:520px;margin-left:auto;margin-right:auto;line-height:1.8;font-weight:300}
.cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.btn-cta-w{padding:16px 38px;background:#fff;color:var(--rose);border-radius:50px;font-weight:700;font-size:.88rem;letter-spacing:.1em;text-transform:uppercase;transition:all .3s;box-shadow:0 6px 24px rgba(255,255,255,.15)}
.btn-cta-w:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(255,255,255,.25)}
.btn-cta-ghost{padding:16px 36px;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:50px;font-size:.88rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;transition:all .3s}
.btn-cta-ghost:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.6);transform:translateY(-3px)}

/* ── FOOTER ── */
footer{background:#080507;color:rgba(255,255,255,.5);padding:68px 56px 36px}
.footer-inner{max-width:1360px;margin:auto}
.footer-grid{display:grid;grid-template-columns:2.2fr 1fr 1fr 1fr;gap:48px;margin-bottom:56px}
@media(max-width:768px){.footer-grid{grid-template-columns:1fr 1fr}.footer-brand-col{grid-column:span 2}footer{padding:48px 24px 28px}}
.footer-logo-row{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.footer-brand-name{font-family:'Bodoni Moda',serif;font-size:1.15rem;font-weight:900;color:#fff;line-height:1.1}
.footer-brand-name em{font-style:italic;color:var(--rose)}
.footer-tagline{font-size:.58rem;color:var(--gold);letter-spacing:.18em;text-transform:uppercase;margin-top:2px}
.footer-brand-col p{font-size:.83rem;line-height:1.8;max-width:280px}
.footer-col h4{font-family:'Bodoni Moda',serif;font-weight:700;color:#fff;margin-bottom:18px;font-size:.9rem;font-style:italic;letter-spacing:.04em}
.footer-col a{display:block;font-size:.82rem;margin-bottom:11px;color:rgba(255,255,255,.42);transition:.25s}
.footer-col a:hover{color:var(--rose)}
.footer-bottom{border-top:1px solid rgba(212,100,122,.1);padding-top:26px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;font-size:.76rem}
.social-links{display:flex;gap:10px;margin-top:18px}
.social-link{width:36px;height:36px;border-radius:10px;background:rgba(212,100,122,.1);border:1px solid rgba(212,100,122,.18);display:flex;align-items:center;justify-content:center;font-size:.95rem;transition:.25s}
.social-link:hover{background:rgba(212,100,122,.25);border-color:rgba(212,100,122,.5)}

/* ── PAGE TRANSITION ── */
.page-transition{position:fixed;inset:0;z-index:99999;pointer-events:none;display:flex;align-items:center;justify-content:center}
.pt-panel{position:absolute;inset:0;background:linear-gradient(135deg,var(--noir),var(--rose));transform:scaleY(0);transform-origin:bottom;transition:transform .55s cubic-bezier(.77,0,.18,1)}
.pt-logo{position:relative;z-index:2;opacity:0;transform:scale(.5);transition:all .4s ease .25s;text-align:center}
.pt-icon{font-size:2.8rem;display:block;margin-bottom:8px}
.pt-text{font-family:'Bodoni Moda',serif;font-weight:900;font-size:.95rem;color:#fff;letter-spacing:.14em;font-style:italic}
.pt-bar{width:0;height:1.5px;background:linear-gradient(90deg,var(--gold2),var(--rose));border-radius:2px;margin:12px auto 0;transition:width .5s ease .3s}
.page-transition.active .pt-panel{transform:scaleY(1)}
.page-transition.active .pt-logo{opacity:1;transform:scale(1)}
.page-transition.active .pt-bar{width:100px}
.ripple-fx{position:fixed;border-radius:50%;background:rgba(212,100,122,.18);transform:scale(0);animation:rippleOut .65s ease-out forwards;pointer-events:none;z-index:9998}
@keyframes rippleOut{to{transform:scale(8);opacity:0}}
</style>
</head>
<body>

<!-- Cursor -->
<div id="cur-dot"></div>
<div id="cur-ring"></div>

<!-- Petals -->
<div class="petals" id="petalsWrap"></div>

<!-- ── NAVBAR ── -->
<nav class="nav" id="navbar">
  <div class="nav-inner">
    <!-- LOGO -->
    <a href="#" class="nav-logo">
      <div class="logo-svg-wrap">
        <!-- SVG Logo: stylized M with rose -->
        <svg class="logo-monogram" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="24" cy="24" r="23" fill="rgba(212,100,122,0.12)" stroke="rgba(212,100,122,0.35)" stroke-width="1"/>
          <!-- M letterform -->
          <path d="M10 34V14L18 27L24 16L30 27L38 14V34" stroke="url(#logoGrad)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
          <!-- small rose accent -->
          <circle cx="24" cy="10" r="2.5" fill="url(#roseGrad)"/>
          <path d="M22.5 10 Q24 7 25.5 10 Q24 11 22.5 10Z" fill="rgba(212,100,122,0.6)"/>
          <path d="M24 8.5 Q27 10 25.5 11.5 Q24 11 24 8.5Z" fill="rgba(200,169,106,0.6)"/>
          <defs>
            <linearGradient id="logoGrad" x1="10" y1="14" x2="38" y2="34" gradientUnits="userSpaceOnUse">
              <stop stop-color="#e8c87a"/>
              <stop offset="0.5" stop-color="#d4647a"/>
              <stop offset="1" stop-color="#c9a0a8"/>
            </linearGradient>
            <radialGradient id="roseGrad" cx="50%" cy="50%" r="50%">
              <stop stop-color="#d4647a"/>
              <stop offset="1" stop-color="#b84060"/>
            </radialGradient>
          </defs>
        </svg>
      </div>
      <div class="logo-text-block">
        <div class="logo-name">Marguax <em>Collections</em></div>
        <div class="logo-tagline">✦ Fashion Boutique</div>
      </div>
    </a>

    <div class="nav-links" id="navLinks">
      <a href="#shop" class="nav-link">Shop</a>
      <a href="#brandnew" class="nav-link">Brand New</a>
      <a href="#preowned" class="nav-link">Pre-Owned</a>
      <a href="#how-to-order" class="nav-link">How to Order</a>
      <a href="auth/login.php" class="nav-link">Login</a>
      <a href="auth/register.php" class="nav-link nav-cta">Shop Now ✦</a>
    </div>
    <div class="hamburger" id="hamburger" onclick="toggleNav()">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-grain"></div>
  <div class="hero-blob hb1"></div>
  <div class="hero-blob hb2"></div>
  <div class="hero-blob hb3"></div>
  <div class="hero-inner">
    <!-- Left -->
    <div>
      <h1>
        Style D. Dress<br>
        <span class="line-script">With Me</span>
      </h1>
      <p class="hero-desc">Curated brand-new outfits and pre-loved designer pieces. Every dress tells a story — find yours. Affordable fashion with a luxury feel.</p>
      <div class="hero-btns">
        <a href="#shop" class="btn-hero-rose">✦ Shop Now</a>
        <a href="#preowned" class="btn-hero-ghost">Pre-Owned →</a>
      </div>
    </div>
    <!-- Right -->
    <div class="hero-right">
      <div class="hero-cards-top">
        <img src="images/products/female-scent.jpg" alt="Featured Outfit" onerror="this.style.background='rgba(212,100,122,.15)';this.style.height='290px'">
        <div class="hero-img-overlay">
          <div>
            <div class="hero-img-label">New Arrivals — Summer 2026</div>
            <div class="hero-img-sub">Brand New Collection · Starting ₱599</div>
          </div>
        </div>
      </div>
      <div class="hero-mini-grid">
        <?php
        $isLoggedIn = isset($_SESSION['user_id']);
        $categories = [
          ['label'=>'Dresses','sub'=>'Brand New','icon'=>'👗','slug'=>'dresses'],
          ['label'=>'Tops & Blouses','sub'=>'Brand New','icon'=>'👚','slug'=>'tops'],
          ['label'=>'Pre-Owned','sub'=>'Gently Used','icon'=>'♻️','slug'=>'pre-owned'],
          ['label'=>'Accessories','sub'=>'Complete Look','icon'=>'👜','slug'=>'accessories'],
        ];
        foreach($categories as $cat):
          $href = $isLoggedIn
            ? 'customer/products.php?category='.urlencode($cat['slug'])
            : 'auth/register.php?redirect='.urlencode('customer/products.php?category='.$cat['slug']);
        ?>
        <a href="<?= $href ?>" class="mini-cat" title="<?= $cat['label'] ?>">
          <span class="mini-cat-icon"><?= $cat['icon'] ?></span>
          <div class="mini-cat-label"><?= $cat['label'] ?></div>
          <div class="mini-cat-sub"><?= $cat['sub'] ?></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── RIBBON ── -->
<div class="ribbon">
  <div class="ribbon-track">
    <span class="ribbon-item">Brand New Outfits <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Pre-Owned Designer Pieces <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Affordable Fashion <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Free Styling Tips <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Marguax Collections Boutique <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Brand New Outfits <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Pre-Owned Designer Pieces <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Affordable Fashion <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Free Styling Tips <span class="ribbon-sep">✦</span></span>
    <span class="ribbon-item">Marguax Collections Boutique <span class="ribbon-sep">✦</span></span>
  </div>
</div>

<!-- ── SHOP CATEGORIES ── -->
<section class="sec shop-cats" id="shop">
  <div class="sec-inner">
    <div class="t-center reveal">
      <div class="eyebrow">Shop by Category</div>
      <h2 class="sec-title">Find Your <em>Perfect Look</em></h2>
      <p class="sec-sub">From everyday chic to special occasion glamour — browse our curated collections.</p>
    </div>
    <div class="cats-grid reveal">
      <div class="cat-card">
        <img src="images/products/female-scent.jpg" alt="Dresses" onerror="this.style.background='linear-gradient(135deg,#fce4ec,#ffd6e8)';this.style.height='100%'">
        <div class="cat-overlay">
          <span class="cat-pill">Brand New</span>
          <div class="cat-name">Dresses</div>
          <div class="cat-count">85+ pieces</div>
        </div>
        <div class="cat-arrow">→</div>
      </div>
      <div class="cat-card">
        <img src="images/products/boosters.jpg" alt="Tops & Blouses" onerror="this.style.background='linear-gradient(135deg,#f5dde2,#c9a0a8)';this.style.height='100%'">
        <div class="cat-overlay">
          <span class="cat-pill">Brand New</span>
          <div class="cat-name">Tops & Blouses</div>
          <div class="cat-count">120+ pieces</div>
        </div>
        <div class="cat-arrow">→</div>
      </div>
      <div class="cat-card">
        <img src="images/products/immukira.jpg" alt="Pre-Owned" onerror="this.style.background='linear-gradient(135deg,#e8c87a,#c8a96a)';this.style.height='100%'">
        <div class="cat-overlay">
          <span class="cat-pill" style="background:linear-gradient(135deg,var(--gold),#a07830)">Pre-Owned</span>
          <div class="cat-name">Designer Finds</div>
          <div class="cat-count">200+ pieces</div>
        </div>
        <div class="cat-arrow">→</div>
      </div>
      <div class="cat-card">
        <img src="images/products/soap.jpg" alt="Accessories" onerror="this.style.background='linear-gradient(135deg,#c9a0a8,#9c6b7a)';this.style.height='100%'">
        <div class="cat-overlay">
          <span class="cat-pill" style="background:rgba(255,255,255,.2);backdrop-filter:blur(6px)">Accessories</span>
          <div class="cat-name">Bags & More</div>
          <div class="cat-count">60+ items</div>
        </div>
        <div class="cat-arrow">→</div>
      </div>
    </div>
  </div>
</section>

<!-- ── BRAND NEW ARRIVALS ── -->
<section class="sec brandnew-bg" id="brandnew">
  <div class="sec-inner">
    <div class="t-center reveal">
      <div class="eyebrow">New Arrivals</div>
      <h2 class="sec-title">Brand New <em>Collection</em></h2>
      <p class="sec-sub">Fresh-from-the-rack outfits. Tags still on. Your wardrobe refresh starts here.</p>
    </div>
    <div class="products-grid">
      <div class="prod-card reveal">
        <div class="prod-img-wrap">
          <img src="images/products/female-scent.jpg" alt="Floral Maxi Dress" onerror="this.style.background='#fce4ec';this.style.height='300px'">
          <span class="prod-badge badge-new">Brand New</span>
          <div class="prod-wish">🤍</div>
        </div>
        <div class="prod-body">
          <div class="prod-tag">Dresses</div>
          <div class="prod-name">Floral Maxi Dress</div>
          <div class="prod-desc">Lightweight chiffon with a romantic floral print. Perfect for beach getaways or casual dates.</div>
          <div class="prod-foot">
            <div class="prod-price">₱850</div>
            <a href="auth/register.php" class="prod-btn">Add to Cart</a>
          </div>
        </div>
      </div>
      <div class="prod-card reveal">
        <div class="prod-img-wrap">
          <img src="images/products/123.png" alt="Satin Slip Dress" onerror="this.style.background='#fce4ec';this.style.height='300px'">
          <span class="prod-badge badge-new">Brand New</span>
          <span class="prod-badge badge-sale" style="left:auto;right:14px">SALE</span>
          <div class="prod-wish">🤍</div>
        </div>
        <div class="prod-body">
          <div class="prod-tag">Dresses</div>
          <div class="prod-name">Satin Slip Dress</div>
          <div class="prod-desc">Elegant cowl-neck satin dress in blush pink. Flattering silhouette for every body type.</div>
          <div class="prod-foot">
            <div class="prod-price">₱1,200 <span class="was">₱1,800</span></div>
            <a href="auth/register.php" class="prod-btn">Add to Cart</a>
          </div>
        </div>
      </div>
      <div class="prod-card reveal">
        <div class="prod-img-wrap">
          <img src="images/products/boosters.jpg" alt="Linen Co-ord Set" onerror="this.style.background='#fce4ec';this.style.height='300px'">
          <span class="prod-badge badge-new">Brand New</span>
          <div class="prod-wish">🤍</div>
        </div>
        <div class="prod-body">
          <div class="prod-tag">Sets</div>
          <div class="prod-name">Linen Co-ord Set</div>
          <div class="prod-desc">Breathable linen top + wide-leg pants set. Effortlessly chic from morning coffee to dinner.</div>
          <div class="prod-foot">
            <div class="prod-price">₱1,450</div>
            <a href="auth/register.php" class="prod-btn">Add to Cart</a>
          </div>
        </div>
      </div>
      <div class="prod-card reveal">
        <div class="prod-img-wrap">
          <img src="images/products/oil.jpg" alt="Embroidered Blouse" onerror="this.style.background='#fce4ec';this.style.height='300px'">
          <span class="prod-badge badge-new">Brand New</span>
          <div class="prod-wish">🤍</div>
        </div>
        <div class="prod-body">
          <div class="prod-tag">Tops</div>
          <div class="prod-name">Embroidered Blouse</div>
          <div class="prod-desc">Delicate hand-embroidered details on crisp cotton. Pairs beautifully with jeans or midi skirts.</div>
          <div class="prod-foot">
            <div class="prod-price">₱680</div>
            <a href="auth/register.php" class="prod-btn">Add to Cart</a>
          </div>
        </div>
      </div>
    </div>
    <div style="text-align:center;margin-top:44px">
      <a href="auth/register.php" class="btn-hero-rose" style="display:inline-flex">👗 Browse All New Arrivals →</a>
    </div>
  </div>
</section>

<!-- ── PRE-OWNED SECTION ── -->
<section class="sec preowned-bg" id="preowned">
  <div class="sec-inner">
    <div class="preowned-inner">
      <!-- Images -->
      <div class="reveal" style="position:relative">
        <div class="preowned-imgs">
          <div class="po-float-tag">Pre-Owned ♻️</div>
          <div class="po-img-main">
            <img src="images/products/gold-package.jpg" alt="Pre-owned fashion" onerror="this.style.background='rgba(212,100,122,.15)';this.style.height='280px'">
          </div>
          <div class="po-img-sm">
            <img src="images/products/female-scent.jpg" alt="Pre-owned dress" onerror="this.style.background='rgba(212,100,122,.1)';this.style.height='180px'">
          </div>
          <div class="po-img-sm">
            <img src="images/products/immukira.jpg" alt="Pre-owned outfit" onerror="this.style.background='rgba(200,169,106,.1)';this.style.height='180px'">
          </div>
        </div>
      </div>
      <!-- Content -->
      <div class="preowned-content reveal">
        <div class="eyebrow">Pre-Owned</div>
        <h2 class="sec-title">Gently Loved,<br><em>Still Beautiful</em></h2>
        <p class="sec-sub">Shop pre-owned designer and branded pieces at a fraction of the price. Sustainable fashion that's still stunning.</p>
        <div class="po-perks">
          <div class="po-perk">
            <div class="po-perk-icon">✅</div>
            <div>
              <div class="po-perk-title">Authenticated & Inspected</div>
              <div class="po-perk-desc">Every pre-owned item is checked for quality before listing.</div>
            </div>
          </div>
          <div class="po-perk">
            <div class="po-perk-icon">💸</div>
            <div>
              <div class="po-perk-title">Up to 70% Off Retail</div>
              <div class="po-perk-desc">Designer looks without the designer price tag.</div>
            </div>
          </div>
          <div class="po-perk">
            <div class="po-perk-icon">♻️</div>
            <div>
              <div class="po-perk-title">Sustainable Shopping</div>
              <div class="po-perk-desc">Give beautiful clothes a second life and reduce fashion waste.</div>
            </div>
          </div>
          <div class="po-perk">
            <div class="po-perk-icon">📦</div>
            <div>
              <div class="po-perk-title">Carefully Packaged</div>
              <div class="po-perk-desc">Arrives clean, pressed, and wrapped with love.</div>
            </div>
          </div>
        </div>
        <div class="po-btns">
          <a href="auth/register.php" class="btn-hero-rose">Shop Pre-Owned →</a>
          <a href="auth/register.php" class="btn-hero-ghost">Sell Your Clothes</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── HOW TO ORDER ── -->
<section class="sec howorder-bg" id="how-to-order">
  <div class="sec-inner">
    <div class="t-center reveal">
      <div class="eyebrow">How to Order</div>
      <h2 class="sec-title">Shopping Made <em>Simple</em></h2>
      <p class="sec-sub">Get your dream outfit in just a few easy steps.</p>
    </div>
    <div class="steps-row">
      <div class="step-card reveal">
        <div class="step-num">1</div>
        <span class="step-icon">👗</span>
        <div class="step-title">Browse & Pick</div>
        <div class="step-desc">Browse our brand-new and pre-owned collections. Filter by size, style, or price to find your perfect match.</div>
      </div>
      <div class="step-card reveal">
        <div class="step-num">2</div>
        <span class="step-icon">🛒</span>
        <div class="step-title">Add to Cart</div>
        <div class="step-desc">Add your favorites to cart. Create a free account for faster checkout and order tracking.</div>
      </div>
      <div class="step-card reveal">
        <div class="step-num">3</div>
        <span class="step-icon">💳</span>
        <div class="step-title">Pay Securely</div>
        <div class="step-desc">Choose from GCash, PayMaya, PayPal, Cash on Pickup, or Cash on Delivery.</div>
      </div>
      <div class="step-card reveal">
        <div class="step-num">4</div>
        <span class="step-icon">🎀</span>
        <div class="step-title">Wear & Shine</div>
        <div class="step-desc">Your order arrives beautifully packaged. Wear it, love it, and share your look with us!</div>
      </div>
    </div>
  </div>
</section> 

<!-- ── CTA ── -->
<section class="cta-section">
  <h2>Your Dream Wardrobe<br><em>Awaits You</em></h2>
  <p>Shop brand-new outfits and pre-loved designer pieces. Affordable. Curated. Beautiful.</p>
  <div class="cta-btns">
    <a href="auth/register.php" class="btn-cta-w">✦ Start Shopping</a>
    <a href="#packages" class="btn-cta-ghost">View Reseller Packages →</a>
  </div>
</section>

<!-- ── FOOTER ── -->
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand-col">
        <div class="footer-logo-row">
          <svg width="40" height="40" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="24" cy="24" r="23" fill="rgba(212,100,122,0.1)" stroke="rgba(212,100,122,0.3)" stroke-width="1"/>
            <path d="M10 34V14L18 27L24 16L30 27L38 14V34" stroke="url(#fLogoGrad)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            <circle cx="24" cy="10" r="2.5" fill="#d4647a"/>
            <defs>
              <linearGradient id="fLogoGrad" x1="10" y1="14" x2="38" y2="34" gradientUnits="userSpaceOnUse">
                <stop stop-color="#e8c87a"/><stop offset="0.5" stop-color="#d4647a"/><stop offset="1" stop-color="#c9a0a8"/>
              </linearGradient>
            </defs>
          </svg>
          <div>
            <div class="footer-brand-name">Marguax <em>Collections</em></div>
            <div class="footer-tagline">✦ Fashion Boutique</div>
          </div>
        </div>
        <p>Your one-stop boutique for brand-new outfits and pre-owned designer pieces. Affordable fashion, luxury feel.</p>
        <div class="social-links">
          <a href="https://facebook.com/Marguaxworldmktg" class="social-link">📘</a>
          <a href="#" class="social-link">📸</a>
          <a href="#" class="social-link">🎵</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Shop</h4>
        <a href="auth/register.php">New Arrivals</a>
        <a href="auth/register.php">Dresses</a>
        <a href="auth/register.php">Tops & Blouses</a>
        <a href="auth/register.php">Sets & Co-ords</a>
        <a href="auth/register.php">Accessories</a>
      </div>
      <div class="footer-col">
        <h4>Pre-Owned</h4>
        <a href="auth/register.php">Designer Finds</a>
        <a href="auth/register.php">Branded Pieces</a>
        <a href="auth/register.php">Sell Your Clothes</a>
        <a href="auth/register.php">How It Works</a>
      </div>
      <div class="footer-col">
        <h4>Account</h4>
        <a href="#how-to-order">How to Order</a>
        <a href="#packages">Reseller Packages</a>
        <a href="auth/login.php">Login</a>
        <a href="auth/register.php">Register</a>
        <a href="https://www.awmc.io">www.awmc.io</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Marguax Collections Fashion Boutique. All rights reserved.</span>
      <span>📘 Marguaxworldmktg · 🌐 www.awmc.io</span>
    </div>
  </div>
</footer>

<!-- Page Transition -->
<div class="page-transition" id="pageTransition">
  <div class="pt-panel"></div>
  <div class="pt-logo">
    <span class="pt-icon">👗</span>
    <div class="pt-text">Marguax Collections</div>
    <div class="pt-bar"></div>
  </div>
</div>

<script>
// ── Cursor ──
const dot=document.getElementById('cur-dot'),ring=document.getElementById('cur-ring');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY});
(function tick(){
  dot.style.left=mx+'px';dot.style.top=my+'px';
  rx+=(mx-rx)*.14;ry+=(my-ry)*.14;
  ring.style.left=rx+'px';ring.style.top=ry+'px';
  requestAnimationFrame(tick);
})();
document.querySelectorAll('a,button,.cat-card,.prod-card,.pkg-card,.po-perk,.mini-cat,.pay-card,.testi-card').forEach(el=>{
  el.addEventListener('mouseenter',()=>{ring.style.width='54px';ring.style.height='54px';ring.style.borderColor='var(--rose)'});
  el.addEventListener('mouseleave',()=>{ring.style.width='38px';ring.style.height='38px';ring.style.borderColor='rgba(212,100,122,.55)'});
});

// ── Petals ──
const pw=document.getElementById('petalsWrap');
const colors=['rgba(212,100,122,.7)','rgba(245,221,226,.8)','rgba(200,169,106,.6)','rgba(201,160,168,.7)'];
for(let i=0;i<22;i++){
  const p=document.createElement('div');p.className='petal';
  p.style.cssText=`left:${Math.random()*100}vw;width:${7+Math.random()*10}px;height:${10+Math.random()*14}px;background:${colors[i%colors.length]};animation-duration:${10+Math.random()*14}s;animation-delay:${Math.random()*20}s;transform:rotate(${Math.random()*360}deg)`;
  pw.appendChild(p);
}

// ── Navbar ──
window.addEventListener('scroll',()=>document.getElementById('navbar').classList.toggle('scrolled',scrollY>50));
function toggleNav(){document.getElementById('navLinks').classList.toggle('open')}

// ── Smooth scroll ──
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click',e=>{
    const t=document.querySelector(a.getAttribute('href'));
    if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth',block:'start'});}
    document.getElementById('navLinks').classList.remove('open');
  });
});

// ── Reveal ──
const obs=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('visible')}),{threshold:.09});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));

// ── Transition ──
const tr=document.getElementById('pageTransition');
function triggerTransition(url){tr.classList.add('active');setTimeout(()=>window.location.href=url,1300)}
document.querySelectorAll('a[href]').forEach(link=>{
  const href=link.getAttribute('href');
  if(!href||href.startsWith('#')||href.startsWith('http')||href.startsWith('mailto'))return;
  link.addEventListener('click',function(e){e.preventDefault();triggerTransition(this.href)});
});

// ── Ripple ──
document.addEventListener('click',e=>{
  const r=document.createElement('div'),s=60;
  r.className='ripple-fx';
  r.style.cssText=`width:${s}px;height:${s}px;left:${e.clientX-s/2}px;top:${e.clientY-s/2}px`;
  document.body.appendChild(r);setTimeout(()=>r.remove(),700);
});
window.addEventListener('pageshow',()=>tr.classList.remove('active'));
</script>
</body>
</html>
