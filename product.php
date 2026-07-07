<?php
require_once 'config.php';

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    header("Location: index.php");
    exit;
}

// Gather all valid images for the gallery
$images = [$product['image']];
for ($i = 1; $i <= 3; $i++) {
    if (!empty($product['image_' . $i])) {
        $images[] = $product['image_' . $i];
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | NightCake</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --bg-color: <?= htmlspecialchars($theme_bg_color) ?> !important;
        }
        body {
            background-color: var(--bg-color);
            transition: background-color 0.5s ease;
        }
        /* Specific Styles for Product Detail Page */
        .product-detail-container {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 4rem;
            margin-top: 2rem;
            margin-bottom: 4rem;
        }
        .gallery-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .main-preview {
            width: 100%;
            height: 500px;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-subtle);
        }
        .main-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }
        .thumbnails-row {
            display: flex;
            gap: 1rem;
        }
        .thumbnail {
            width: 100px;
            height: 100px;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: var(--transition);
        }
        .thumbnail:hover, .thumbnail.active {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-info-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1rem 0;
        }
        .detail-category {
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
        }
        .detail-title {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .detail-price {
            font-family: var(--font-serif);
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-main);
        }
        .detail-desc {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.8;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 2rem;
        }
        .qty-label {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .qty-selector {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 50px;
            background-color: var(--secondary-color);
            padding: 0.3rem;
        }
        .qty-btn {
            background: transparent;
            border: none;
            width: 35px;
            height: 35px;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }
        .qty-btn:hover {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .qty-val {
            width: 40px;
            text-align: center;
            font-weight: 600;
        }
        .breadcrumb {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }
        .breadcrumb a:hover {
            color: var(--primary-color);
        }
        @media (max-width: 768px) {
            .product-detail-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .main-preview {
                height: 350px;
            }
        }
    </style>
</head>
<body>

<div class="announcement-bar">
    🎂 ฉลองวันพิเศษของคุณด้วยโบว์เค้กมินิมอล บริการเขียนหน้าเค้กฟรี! สั่งล่วงหน้าและจัดส่งเดลิเวอรี่ทั่วกรุงเทพฯ
</div>

<header>
    <a href="index.php" class="logo">NightCake<span>.</span></a>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <a href="index.php#products">เค้กทั้งหมด</a>
        <a href="index.php#contact">ติดต่อเรา / สาขา</a>
        <a href="#" id="cart-toggle-nav" style="display: flex; align-items: center; gap: 0.4rem; font-weight: 500; margin-right: 0.8rem;">
            🛒 ตะกร้าสินค้า (<span id="cart-count-nav">0</span>)
        </a>
        
        <!-- User Login/Register Tabber -->
        <span class="user-auth-nav" style="border-left: 1.5px solid var(--border-color, #f1e1e1); padding-left: 0.8rem; display: inline-flex; align-items: center; gap: 0.8rem;">
            <?php if(isset($_SESSION['user_id'])): 
                $nav_avatar = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="30" height="30"><rect width="100" height="100" rx="15" fill="%23ffe6eb"/><circle cx="50" cy="40" r="20" fill="%23f7a8b8"/><path d="M20 80c0-15 15-25 30-25s30 10 30 25z" fill="%23f7a8b8"/></svg>';
                if (!empty($_SESSION['profile_pic']) && file_exists($_SESSION['profile_pic'])) {
                    $nav_avatar = $_SESSION['profile_pic'];
                }
            ?>
                <a href="profile.php" style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-main); font-weight: 500; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-color)'" onmouseout="this.style.color='var(--text-main)'">
                    <img src="<?= htmlspecialchars($nav_avatar) ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 6px; border: 1.5px solid var(--primary-color);" alt="Profile">
                    <span>สวัสดี, <?= htmlspecialchars($_SESSION['username']) ?></span>
                </a>
                <a href="logout.php" style="font-size: 0.85rem; color: var(--text-light); font-weight: 500;">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary btn-line" style="padding: 0.35rem 0.9rem; font-size: 0.8rem; box-shadow: none; border-radius: 20px; text-decoration: none;">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn" style="padding: 0.35rem 0.9rem; font-size: 0.8rem; border-radius: 20px; box-shadow: none; color: white; text-decoration: none;">สมัครสมาชิก</a>
            <?php endif; ?>
        </span>
    </nav>
</header>

<main class="container">
    <div class="breadcrumb">
        <a href="index.php">หน้าแรก</a> &gt; 
        <a href="index.php#products">เค้กทั้งหมด</a> &gt; 
        <span><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <div class="product-detail-container">
        <!-- Left: Gallery -->
        <div class="gallery-container">
            <div class="main-preview">
                <img id="main-preview-img" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="thumbnails-row">
                <?php foreach($images as $index => $img): ?>
                    <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="changePreview('<?= htmlspecialchars($img) ?>', this)">
                        <img src="<?= htmlspecialchars($img) ?>" alt="Thumbnail <?= $index + 1 ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Info -->
        <div class="product-info-column">
            <span class="detail-category">
                <?php
                    $cat = $product['category'] ?? 'minimalist';
                    if ($cat == 'minimalist') echo 'เค้กมินิมอล';
                    elseif ($cat == 'coconut') echo 'เค้กมะพร้าว & ผลไม้';
                    elseif ($cat == 'chocolate') echo 'ช็อกโกแลตฟัดจ์';
                    else echo 'เค้กโบราณ & อื่นๆ';
                ?>
            </span>
            <h1 class="detail-title"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="detail-price">฿<?= number_format($product['price'], 2) ?></div>
            <p class="detail-desc"><?= htmlspecialchars($product['description']) ?></p>
            
            <div style="display: flex; align-items: center; gap: 2rem; margin-top: 1rem;">
                <span class="qty-label">จำนวน:</span>
                <div class="qty-selector">
                    <button class="qty-btn" onclick="adjustQty(-1)">-</button>
                    <span class="qty-val" id="qty-value">1</span>
                    <button class="qty-btn" onclick="adjustQty(1)">+</button>
                </div>
            </div>

            <button class="btn" style="margin-top: 2rem; padding: 1rem;" 
                    onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= $product['price'] ?>, '<?= htmlspecialchars($product['image']) ?>')">
                เพิ่มสินค้าในตะกร้า 🛒
            </button>
        </div>
    </div>
</main>

<footer>
    <div class="footer-grid">
        <div id="logo-nightcake" style="cursor: default;">
            <div class="footer-logo">NightCake<span>.</span></div>
            <p class="footer-desc">ความสุขก้อนใหญ่ ดีไซน์มินิมอล อบด้วยความใส่ใจและวัตถุดิบชั้นเลิศเพื่อคนสำคัญของคุณในทุกๆ วันพิเศษ</p>
        </div>
        <div>
            <h4 class="footer-title">เมนูเค้ก</h4>
            <ul class="footer-links">
                <li><a href="index.php#products">เค้กมินิมอลพาสเทล</a></li>
                <li><a href="index.php#products">เค้กมะพร้าวครีมสด</a></li>
                <li><a href="index.php#products">เค้กช็อกโกแลตฟัดจ์ในตำนาน</a></li>
                <li><a href="index.php#products">คอลเลกชันเค้กวันเกิด</a></li>
            </ul>
        </div>
        <div>
            <h4 class="footer-title">สาขายอดนิยม</h4>
            <ul class="footer-links">
                <li><a href="#">สาขา สยามสแควร์</a></li>
                <li><a href="#">สาขา เซ็นทรัล ลาดพร้าว</a></li>
                <li><a href="#">สาขา เมกา บางนา</a></li>
                <li><a href="#">สาขา สามย่าน มิตรทาวน์</a></li>
            </ul>
        </div>
        <div>
            <h4 class="footer-title">การติดต่อ</h4>
            <ul class="footer-links">
                <li><a href="#">Line: @NIGHTCAKE</a></li>
                <li><a href="#">โทร: 094-492-2299</a></li>
                <li><a href="#">Facebook: NightCake</a></li>
                <li><a href="#">Instagram: @nightcake</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 NightCake Shop. All Rights Reserved.</p>
        <p>Minimalist Cake & Birthday Event Specialist</p>
    </div>
</footer>

<!-- Include Cart Drawer markup -->
<?php include_once 'cart_drawer.php'; ?>

<script>
    // Simple gallery changer
    function changePreview(imgSrc, thumbElem) {
        const previewImg = document.getElementById('main-preview-img');
        previewImg.style.opacity = '0';
        
        setTimeout(() => {
            previewImg.src = imgSrc;
            previewImg.style.opacity = '1';
        }, 150);

        // Remove active class from thumbnails
        document.querySelectorAll('.thumbnail').forEach(th => th.classList.remove('active'));
        thumbElem.classList.add('active');
    }

    // Qty adjustments
    let qty = 1;
    function adjustQty(amount) {
        qty += amount;
        if(qty < 1) qty = 1;
        document.getElementById('qty-value').innerText = qty;
    }

    // Add to cart helper function
    function addToCart(id, name, price, img) {
        let cart = JSON.parse(localStorage.getItem('cake_cart')) || [];
        const index = cart.findIndex(item => item.id === id);
        
        if (index > -1) {
            cart[index].qty += qty;
        } else {
            cart.push({ id, name, price, img, qty });
        }
        
        localStorage.setItem('cake_cart', JSON.stringify(cart));
        
        // Reset qty display to 1 after adding
        qty = 1;
        document.getElementById('qty-value').innerText = 1;
        
        // Render updated cart and open drawer
        if(window.renderCart) {
            window.renderCart();
        }
        if(window.openCart) {
            window.openCart();
        }
    }
</script>
<?php include_once 'chatbot_widget.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
