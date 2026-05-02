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

// Fetching all packages from DB
$stmt = $db->prepare("SELECT * FROM products WHERE product_type='package' ORDER BY price ASC");
$stmt->execute();
$packages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Membership — Amazing World Marketing Corp</title>
<link rel="stylesheet" href="../css/style.css">
<style>
:root {
  --navy: #0b1f3a; --blue: #2563eb; --amber: #f59e0b;
  --green: #10b981; --border: #e2e8f0; --radius: 16px;
  --text: #0f172a; --text-2: #475569; --text-3: #94a3b8;
  /* Package Colors from Screenshot */
  --silver-pkg: #6b7280; --gold-pkg: #b45309; --ruby-pkg: #be123c;
  --emerald-pkg: #065f46; --diamond-pkg: #0369a1;
}
body { font-family:'Plus Jakarta Sans','Sora',sans-serif; margin:0; background:#0b1f3a; color: #fff; }
.container { max-width:1100px; margin:auto; padding:24px 16px; }

/* Steps Section */
.steps-section { text-align: center; padding: 60px 0; }
.steps-section h3 { background: #b45309; display: inline-block; padding: 4px 15px; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 15px; }
.steps-section h1 { font-size: 2.5rem; margin-bottom: 40px; }
.steps-grid { display: flex; justify-content: space-between; gap: 20px; position: relative; }
.steps-grid::before { content: ''; position: absolute; top: 25px; left: 10%; right: 10%; height: 2px; background: rgba(255,255,255,0.2); z-index: 1; }
.step-item { flex: 1; z-index: 2; }
.step-number { width: 50px; height: 50px; background: var(--amber); color: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; margin: 0 auto 20px; border: 4px solid #0b1f3a; }
.step-card { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 15px; margin-bottom: 15px; min-height: 150px; display: flex; align-items: center; justify-content: center; }
.step-card img { max-width: 100%; border-radius: 10px; }
.step-item h4 { margin: 10px 0; font-size: 1.2rem; }
.step-item p { font-size: 0.9rem; color: #cbd5e1; line-height: 1.4; }

/* Package Grid */
.packages-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-top: 50px; }
.package-card { background: #fff; border-radius: var(--radius); overflow: hidden; color: #333; transition: 0.3s; }
.package-card:hover { transform: translateY(-10px); }
.pkg-header { padding: 30px 20px; color: #fff; text-align: left; }
.pkg-header h2 { margin: 0; font-size: 1.8rem; }
.pkg-header .price { font-size: 2.2rem; font-weight: 800; }
.pkg-header span { font-size: 0.8rem; opacity: 0.9; }

/* Dynamic Package Colors */
.bg-silver { background: var(--silver-pkg); }
.bg-gold { background: var(--gold-pkg); }
.bg-ruby { background: var(--ruby-pkg); }
.bg-emerald { background: var(--emerald-pkg); }
.bg-diamond { background: var(--diamond-pkg); }

.pkg-body { padding: 25px; }
.pkg-feature { display: flex; gap: 10px; margin-bottom: 10px; font-size: 0.9rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
.pkg-feature .check { color: var(--green); font-weight: bold; }
.roi-badge { background: #ecfdf5; color: #065f46; padding: 8px; border-radius: 8px; font-size: 0.85rem; text-align: center; margin: 15px 0; font-weight: 600; }
.pkg-cta { display: block; width: 100%; padding: 12px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 700; color: #fff; transition: 0.2s; border: none; cursor: pointer; }
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container">
    <section class="steps-section">
        <h3>How to Join</h3>
        <h1>3 Simple Steps to Get Started</h1>
        <div class="steps-grid">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-card">
                    <p style="color:#fff">Attend ABOP Presentation</p>
                </div>
                <h4>Attend ABOP</h4>
                <p>Join our Amazing Business Opportunity Presentation. Talk to our distributors.</p>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-card">
                    <p style="color:#fff">Choose Product Package</p>
                </div>
                <h4>Get Your Package</h4>
                <p>Choose the product package that fits your lifestyle and budget (Silver to Diamond).</p>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-card">
                    <p style="color:#fff">Create Your Account</p>
                </div>
                <h4>Sign Up & Create Account</h4>
                <p>Register on our ordering system and enjoy exclusive member discounts.</p>
            </div>
        </div>
    </section>

    <div class="packages-grid">
        <?php
        // Map Database results to UI Classes
        $config = [
            'Silver'  => ['class' => 'bg-silver', 'roi' => 'P8,466'],
            'Gold'    => ['class' => 'bg-gold', 'roi' => 'P13,200'],
            'Ruby'    => ['class' => 'bg-ruby', 'roi' => 'P25,200'],
            'Emerald' => ['class' => 'bg-emerald', 'roi' => 'P62,000'],
            'Diamond' => ['class' => 'bg-diamond', 'roi' => 'P125,000'],
        ];

        foreach ($packages as $pkg):
            $name = $pkg['product_name'];
            // Find key in config (e.g., if name contains "Silver")
            $theme = ['class' => 'bg-navy', 'roi' => 'N/A'];
            foreach($config as $key => $val) {
                if(stripos($name, $key) !== false) { $theme = $val; break; }
            }
        ?>
        <div class="package-card">
            <div class="pkg-header <?= $theme['class'] ?>">
                <h2><?= htmlspecialchars($name) ?></h2>
                <div class="price">₱<?= number_format($pkg['price'], 0) ?></div>
                <span>one-time</span>
            </div>
            <div class="pkg-body">
                <div class="pkg-feature"><span class="check">✓</span> 100% Product Value</div>
                <div class="pkg-feature"><span class="check">✓</span> Full Member Benefits</div>
                <div class="pkg-feature"><span class="check">✓</span> Online Dashboard Access</div>
                
                <div class="roi-badge"> ROI up to <?= $theme['roi'] ?></div>

                <?php if ($isMember): ?>
                    <button class="pkg-cta <?= $theme['class'] ?>" style="opacity:0.6" disabled>Already a Member</button>
                <?php else: ?>
                    <a href="cart.php?add=<?= (int)$pkg['product_id'] ?>" class="pkg-cta <?= $theme['class'] ?>">
                        Get <?= explode(' ', $name)[0] ?> Package
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>