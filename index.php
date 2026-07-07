<?php
require_once 'config.php';

$stmt = $pdo->query("
    SELECT p.*, c.slug AS category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for dynamic tabs
$stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NightCake | ร้านเค้กวันเกิดและเค้กมินิมอลพรีเมียมครีมสด</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --bg-color: <?= htmlspecialchars($theme_bg_color) ?> !important;
            --primary-color: <?= htmlspecialchars($theme_primary_color) ?> !important;
            --text-main: <?= htmlspecialchars($theme_text_color) ?> !important;
            --text-muted: <?= htmlspecialchars($theme_muted_text_color) ?> !important;
            
            /* Automatically adapt other theme states to the new primary color using CSS color-mix */
            --primary-hover: color-mix(in srgb, var(--primary-color) 85%, #000) !important;
            --secondary-color: color-mix(in srgb, var(--primary-color) 6%, #fff) !important;
            --accent-color: color-mix(in srgb, var(--primary-color) 30%, #fff) !important;
            --border-color: color-mix(in srgb, var(--primary-color) 15%, #fff) !important;
            --shadow-subtle: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
            --shadow-hover: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            transition: background-color 0.5s ease, color 0.5s ease;
        }
        
        /* Force sideways flex layout for hero slider to override cached css */
        .hero-slider .slider-wrapper {
            display: flex !important;
            position: relative !important;
            height: 100% !important;
            transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .hero-slider .slide {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            opacity: 1 !important;
            z-index: auto !important;
            flex-shrink: 0 !important;
            height: 100% !important;
            transition: none !important;
        }
        .hero-slider .slide img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover) !important;
            border-color: var(--primary-color) !important;
        }
        
        /* Interactive Announcement Bar */
        .interactive-ann-bar {
            cursor: pointer;
            overflow: hidden;
            transition: background-color 0.3s ease;
            position: relative;
            background-color: var(--accent-color);
            color: color-mix(in srgb, var(--primary-color) 80%, #000) !important;
        }
        .interactive-ann-bar:hover .ann-cake-icon {
            display: inline-block;
            transform: scale(1.25) rotate(15deg);
        }
        .ann-cake-icon {
            display: inline-block;
            transition: transform 0.3s ease;
        }
        /* Keep editable logo text charcoal, and only keep the dot pink */
        .logo #editable-logo-text, .footer-logo #editable-footer-logo-text {
            color: var(--text-main) !important;
            font-weight: 700;
        }
        .logo span:not(#editable-logo-text), .footer-logo span:not(#editable-footer-logo-text) {
            color: #f7a8b8 !important;
            font-weight: 400;
        }
        
        /* Pagination Buttons styling */
        .pagination-btn {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            border: 1.5px solid var(--border-color);
            background: white;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            color: var(--text-main);
        }
        .pagination-btn.active {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(247, 168, 184, 0.3);
        }
        .pagination-btn:hover:not(.active):not(:disabled) {
            background-color: var(--secondary-color);
            border-color: var(--primary-color);
        }
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<!-- Announcement Bar -->
<div class="announcement-bar interactive-ann-bar" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
    <span class="<?= isset($_GET['customizer_preview']) ? 'editable-icon-selector' : '' ?>" id="editable-ann-icon" onclick="openIconPicker(event, 'ann')" style="cursor: pointer; display: inline-flex; align-items: center; border-radius: 50%; padding: 2px; line-height: 1; transition: transform 0.3s ease;" class="ann-cake-icon"><?= $theme_announcement_icon ?></span>
    <span id="editable-ann-text" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?> style="outline-offset: 2px;"><?= $theme_announcement_text ?></span>
</div>

<header>
    <a href="index.php" class="logo">
        <span id="editable-logo-text" style="display: inline-block; min-width: 50px;"><?= htmlspecialchars($theme_logo_text) ?></span><span>.</span>
    </a>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <a href="#products">เค้กทั้งหมด</a>
        <a href="#contact">ติดต่อเรา / สาขา</a>
        <a href="#" id="cart-toggle-nav" style="display: flex; align-items: center; gap: 0.4rem; font-weight: 500;">
            🛒 ตะกร้าสินค้า (<span id="cart-count-nav">0</span>)
        </a>
    </nav>
</header>

<main>
    <!-- Hero Section -->
    <section class="container hero-section">
        <div class="hero-content">
            <h2 id="editable-hero-h2" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_hero_h2 ?></h2>
            <h1 id="editable-hero-h1" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_hero_h1 ?></h1>
            <p id="editable-hero-p" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_hero_p ?></p>
            <div class="hero-buttons">
                <a href="#products" class="btn">สั่งซื้อเค้กเลย</a>
                <a href="https://line.me" target="_blank" class="btn btn-secondary btn-line">สั่งซื้อทาง Line: @NIGHTCAKE</a>
            </div>
        </div>
        <div class="hero-image-container hero-slider">
            <div class="slider-wrapper" style="width: <?= count($theme_hero_slides) * 100 ?>%;">
                <?php foreach ($theme_hero_slides as $index => $slide_path): ?>
                    <div class="slide" style="width: <?= 100 / count($theme_hero_slides) ?>%;">
                        <img <?= $index === 0 ? 'id="hero-live-preview-img"' : '' ?> src="<?= htmlspecialchars($slide_path) ?>" alt="Premium Cake Slide <?= $index + 1 ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Slider navigation dots -->
            <div class="slider-dots">
                <?php foreach ($theme_hero_slides as $index => $slide_path): ?>
                    <span class="dot <?= $index === 0 ? 'active' : '' ?>" onclick="currentSlide(<?= $index ?>)"></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div style="position: relative; display: inline-block;">
                    <span class="feature-icon <?= isset($_GET['customizer_preview']) ? 'editable-icon-selector' : '' ?>" id="editable-f1-icon" onclick="openIconPicker(event, 'f1')"><?= $theme_f1_icon ?></span>
                </div>
                <h3 id="editable-f1-title" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_f1_title ?></h3>
                <p id="editable-f1-desc" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_f1_desc ?></p>
            </div>
            <div class="feature-item">
                <div style="position: relative; display: inline-block;">
                    <span class="feature-icon <?= isset($_GET['customizer_preview']) ? 'editable-icon-selector' : '' ?>" id="editable-f2-icon" onclick="openIconPicker(event, 'f2')"><?= $theme_f2_icon ?></span>
                </div>
                <h3 id="editable-f2-title" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_f2_title ?></h3>
                <p id="editable-f2-desc" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_f2_desc ?></p>
            </div>
            <div class="feature-item">
                <div style="position: relative; display: inline-block;">
                    <span class="feature-icon <?= isset($_GET['customizer_preview']) ? 'editable-icon-selector' : '' ?>" id="editable-f3-icon" onclick="openIconPicker(event, 'f3')"><?= $theme_f3_icon ?></span>
                </div>
                <h3 id="editable-f3-title" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_f3_title ?></h3>
                <p id="editable-f3-desc" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_f3_desc ?></p>
            </div>
        </div>
    </section>

    <!-- Product Grid & Filters -->
    <section class="container" id="products">
        <div class="category-container">
            <h2 class="section-title" id="editable-menu-title" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_menu_title ?></h2>
            <p class="section-subtitle" id="editable-menu-subtitle" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_menu_subtitle ?></p>
            
            <div class="category-tabs">
                <button class="category-tab active" data-filter="all">ทั้งหมด</button>
                <?php foreach($categories as $cat): ?>
                    <button class="category-tab" data-filter="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if(isset($_GET['order']) && $_GET['order'] == 'success'): ?>
            <div class="alert alert-success" style="max-width: 600px; margin: 0 auto 2rem;">
                สั่งซื้อสำเร็จ! เราได้รับข้อมูลเรียบร้อยแล้วและจะติดต่อกลับโดยเร็วที่สุด
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['order']) && $_GET['order'] == 'error'): ?>
            <div class="alert alert-danger" style="max-width: 600px; margin: 0 auto 2rem;">
                เกิดข้อผิดพลาดในการสั่งซื้อ กรุณาลองใหม่อีกครั้ง
            </div>
        <?php endif; ?>

        <div class="product-grid">
            <?php if(count($products) > 0): ?>
                <?php foreach($products as $product): ?>
                    <?php 
                        $category_class = htmlspecialchars($product['category_slug'] ?? 'minimalist');
                    ?>
                    <div class="product-card" data-category="<?= htmlspecialchars($product['category_id'] ?? '') ?>">
                        <div class="product-image-wrap">
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                            <?php if ($category_class == 'minimalist'): ?>
                                <span class="product-badge">Minimalist</span>
                            <?php elseif ($category_class == 'coconut'): ?>
                                <span class="product-badge" style="background-color: #6bc2c3;">Coconut</span>
                            <?php elseif ($category_class == 'chocolate'): ?>
                                <span class="product-badge" style="background-color: #a87f74;">Fudge</span>
                            <?php else: ?>
                                <span class="product-badge" style="background-color: #fca311;">Classic</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="product-footer">
                                <span class="price">฿<?= number_format($product['price'], 2) ?></span>
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn" style="padding: 0.6rem 1.4rem;">สั่งซื้อ</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3rem 0;">ยังไม่มีรายการเค้กในระบบขณะนี้ คุณสามารถเพิ่มรายการเค้กผ่านระบบจัดการหลังบ้านได้ครับ</p>
            <?php endif; ?>
        </div>
        
        <!-- Dynamic Pagination Controls -->
        <div id="product-pagination-container" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3rem; margin-bottom: 1rem;">
            <!-- Rendered by Javascript -->
        </div>
    </section>

    <!-- Customer Reviews Section -->
    <?php
    // Fetch latest 10 reviews
    $stmt_reviews = $pdo->query("SELECT * FROM reviews ORDER BY id DESC LIMIT 10");
    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <section class="container" id="reviews-section" style="border-top: 1px solid var(--border-color); padding-top: 5rem; margin-bottom: 3rem;">
        <div class="category-container" style="margin-bottom: 3.5rem;">
            <h2 class="section-title">รีวิวและความประทับใจจากลูกค้า 💖</h2>
            <p class="section-subtitle">ความรักและคำชมจากลูกค้าคือพลังในการสร้างสรรค์ผลงานของเรา</p>
        </div>

        <div class="reviews-layout" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 4rem; align-items: start;">
            <!-- Left: Reviews List -->
            <div class="reviews-grid-container">
                <?php if (count($reviews) > 0): ?>
                    <div class="reviews-masonry" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-card" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 1.5rem; box-shadow: var(--shadow-subtle); display: flex; flex-direction: column; gap: 0.8rem; transition: var(--transition);">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($rev['customer_name']) ?></strong>
                                    <span style="color: #fca311; font-size: 0.9rem;">
                                        <?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5 - $rev['rating']) ?>
                                    </span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; flex-grow: 1; font-style: italic;">
                                    "<?= htmlspecialchars($rev['comment']) ?>"
                                </p>
                                <span style="font-size: 0.75rem; color: var(--text-light); align-self: flex-end;">
                                    <?= date('d M Y', strtotime($rev['created_at'])) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 3rem 0;">ยังไม่มีรีวิวจากลูกค้าขณะนี้ มาร่วมเป็นคนแรกที่รีวิวให้ร้านเรากันเถอะครับ!</p>
                <?php endif; ?>
            </div>

            <!-- Right: Submit Review Form -->
            <div class="review-form-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 2rem; box-shadow: var(--shadow-subtle);">
                <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.8rem; display: flex; align-items: center; gap: 0.5rem;">✍️ เขียนรีวิวของคุณ</h3>
                
                <?php if (isset($_GET['review_submit']) && $_GET['review_submit'] == 'success'): ?>
                    <div class="alert alert-success" style="padding: 0.6rem 1rem; font-size: 0.8rem; margin-bottom: 1.2rem;">
                        ส่งความคิดเห็นสำเร็จ ขอบพระคุณมากค่ะ! 💖
                    </div>
                <?php endif; ?>

                <form action="submit_review.php" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; font-size: 0.85rem;">ชื่อของคุณ</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="เช่น คุณสมศรี ดีใจ" required style="padding: 0.65rem 1rem; font-size: 0.85rem;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; font-size: 0.85rem; display: block; margin-bottom: 0.3rem;">คะแนนความประทับใจ</label>
                        <div class="rating-stars" style="display: flex; gap: 0.4rem; font-size: 1.3rem; cursor: pointer; color: var(--text-light);">
                            <input type="hidden" name="rating" id="rating-value" value="5" required>
                            <span class="star-btn" data-value="1" onclick="setRating(1)">★</span>
                            <span class="star-btn" data-value="2" onclick="setRating(2)">★</span>
                            <span class="star-btn" data-value="3" onclick="setRating(3)">★</span>
                            <span class="star-btn" data-value="4" onclick="setRating(4)">★</span>
                            <span class="star-btn" data-value="5" onclick="setRating(5)" style="color: #fca311;">★</span>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 500; font-size: 0.85rem;">ข้อความรีวิว / คำแนะนำติชม</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="เขียนความประทับใจของคุณต่อรสชาติเค้กและการจัดส่ง..." required style="padding: 0.65rem 1rem; font-size: 0.85rem; resize: none;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-block" style="padding: 0.7rem; font-size: 0.85rem; margin-top: 0.5rem; box-shadow: none;">ส่งรีวิวเลย 🚀</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Script for interactive star rating selection -->
    <script>
    function setRating(rating) {
        document.getElementById('rating-value').value = rating;
        const stars = document.querySelectorAll('.star-btn');
        stars.forEach(star => {
            const val = parseInt(star.getAttribute('data-value'));
            if (val <= rating) {
                star.style.color = '#fca311';
            } else {
                star.style.color = 'var(--text-light)';
            }
        });
    }
    </script>

    <!-- Info / Branch Section -->
    <section class="container" id="contact">
        <div class="info-section">
            <div class="info-content">
                <h2 id="editable-delivery-h2" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_delivery_h2 ?></h2>
                <p id="editable-delivery-p" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_delivery_p ?></p>
                <div class="contact-details">
                    <div class="contact-item">
                        <span class="contact-icon">📞</span>
                        <span class="contact-label">ติดต่อสอบถาม</span>
                        <span id="editable-delivery-phone" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_delivery_phone ?></span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">💬</span>
                        <span class="contact-label">Line ID</span>
                        <span id="editable-delivery-line" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_delivery_line ?></span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">⏰</span>
                        <span class="contact-label">เวลาทำการ</span>
                        <span id="editable-delivery-hours" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_delivery_hours ?></span>
                    </div>
                </div>
                <a href="https://line.me" target="_blank" class="btn btn-secondary btn-line" style="box-shadow: none;">
                    <span id="editable-delivery-btn" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?> style="outline: none;"><?= $theme_delivery_btn ?></span>
                </a>
            </div>
            <div class="hero-image-container" style="box-shadow: none; aspect-ratio: 1.2; position: relative;">
                <img src="<?= htmlspecialchars($theme_delivery_image) ?>" alt="Delivery Collection" style="border-radius: var(--border-radius-md); width: 100%; height: 100%; object-fit: cover;" id="editable-delivery-image">
                <?php if (isset($_GET['customizer_preview'])): ?>
                    <!-- Overlay for preview upload click -->
                    <div onclick="window.parent.triggerDeliveryImageUpload()" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; border-radius: var(--border-radius-md); cursor: pointer; font-size: 0.95rem; opacity: 0; transition: opacity 0.3s ease; gap: 0.5rem;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                        <span style="font-size: 1.8rem;">📷</span>
                        <strong>คลิกเพื่อเปลี่ยนรูปภาพจัดส่ง</strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<footer>
<div class="footer-grid">
    <div id="admin-secret-logo-footer" style="cursor: pointer;">
        <div class="footer-logo"><span id="editable-footer-logo-text"><?= htmlspecialchars($theme_logo_text) ?></span><span>.</span></div>
        <p class="footer-desc" id="footer_desc_el" <?= isset($_GET['customizer_preview']) ? 'contenteditable="true" class="editable-text-field"' : '' ?>><?= $theme_footer_desc ?></p>
    </div>
        <div>
            <h4 class="footer-title" id="f_menu_title_el"><?= htmlspecialchars($theme_f_menu_title) ?></h4>
            <ul class="footer-links">
                <li><a href="#products" id="f_menu_l1_el"><?= htmlspecialchars($theme_f_menu_l1) ?></a></li>
                <li><a href="#products" id="f_menu_l2_el"><?= htmlspecialchars($theme_f_menu_l2) ?></a></li>
                <li><a href="#products" id="f_menu_l3_el"><?= htmlspecialchars($theme_f_menu_l3) ?></a></li>
                <li><a href="#products" id="f_menu_l4_el"><?= htmlspecialchars($theme_f_menu_l4) ?></a></li>
            </ul>
        </div>
        <div>
            <h4 class="footer-title" id="f_branch_title_el"><?= htmlspecialchars($theme_f_branch_title) ?></h4>
            <ul class="footer-links">
                <li><a href="#" id="f_branch_l1_el"><?= htmlspecialchars($theme_f_branch_l1) ?></a></li>
                <li><a href="#" id="f_branch_l2_el"><?= htmlspecialchars($theme_f_branch_l2) ?></a></li>
                <li><a href="#" id="f_branch_l3_el"><?= htmlspecialchars($theme_f_branch_l3) ?></a></li>
                <li><a href="#" id="f_branch_l4_el"><?= htmlspecialchars($theme_f_branch_l4) ?></a></li>
            </ul>
        </div>
        <div>
            <h4 class="footer-title" id="f_contact_title_el"><?= htmlspecialchars($theme_f_contact_title) ?></h4>
            <ul class="footer-links">
                <li><a href="#" id="f_contact_l1_el"><?= htmlspecialchars($theme_f_contact_l1) ?></a></li>
                <li><a href="#" id="f_contact_l2_el"><?= htmlspecialchars($theme_f_contact_l2) ?></a></li>
                <li><a href="#" id="f_contact_l3_el"><?= htmlspecialchars($theme_f_contact_l3) ?></a></li>
                <li><a href="#" id="f_contact_l4_el"><?= htmlspecialchars($theme_f_contact_l4) ?></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p id="f_copyright_el"><?= htmlspecialchars($theme_f_copyright) ?></p>
        <p id="f_tagline_el"><?= htmlspecialchars($theme_f_tagline) ?></p>
    </div>
</footer>

<!-- Include Cart Drawer markup -->
<?php include_once 'cart_drawer.php'; ?>

<!-- Include Chatbot Widget markup and JS -->
<?php include_once 'chatbot_widget.php'; ?>

<script src="js/script.js?v=<?= time() ?>"></script>
<script>
// Hero Image Slider Script
let currentSlideIndex = 0;
const slides = document.querySelectorAll('.hero-slider .slide');
const dots = document.querySelectorAll('.hero-slider .dot');

function showSlide(index) {
    const wrapper = document.querySelector('.slider-wrapper');
    if (slides.length === 0) return;
    if (index >= slides.length) currentSlideIndex = 0;
    else if (index < 0) currentSlideIndex = slides.length - 1;
    else currentSlideIndex = index;
    
    // Shift wrapper left by percentage of current index
    const shiftPercent = -(currentSlideIndex * (100 / slides.length));
    if (wrapper) {
        wrapper.style.transform = `translateX(${shiftPercent}%)`;
    }
    
    dots.forEach(dot => dot.classList.remove('active'));
    if (dots[currentSlideIndex]) {
        dots[currentSlideIndex].classList.add('active');
    }
}

function nextSlide() {
    showSlide(currentSlideIndex + 1);
}

function currentSlide(index) {
    showSlide(index);
    resetSliderInterval();
}

let sliderInterval = setInterval(nextSlide, 5000); // Auto change slide every 5 seconds

function resetSliderInterval() {
    clearInterval(sliderInterval);
    sliderInterval = setInterval(nextSlide, 5000);
}

// Expose functions to window object so the admin customizer iframe can control the slider
window.showSlide = showSlide;
window.currentSlide = currentSlide;
</script>

<?php if (isset($_GET['customizer_preview'])): ?>
<!-- Inline Emoji Picker HTML element -->
<div id="inline-emoji-picker" style="display: none; position: absolute; z-index: 10000; background: #fff; border: 1.5px solid var(--primary-color); border-radius: 12px; padding: 0.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.15); width: 180px; grid-template-columns: repeat(5, 1fr); gap: 0.3rem;"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Prevent navigation for links in preview mode to avoid iframe reloads
    document.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
        });
    });

    // 1. WYSIWYG text elements
    const fields = {
        'menu_title': document.getElementById('editable-menu-title'),
        'hero_h2': document.getElementById('editable-hero-h2'),
        'hero_h1': document.getElementById('editable-hero-h1'),
        'hero_p': document.getElementById('editable-hero-p'),
        'f1_title': document.getElementById('editable-f1-title'),
        'f1_desc': document.getElementById('editable-f1-desc'),
        'f2_title': document.getElementById('editable-f2-title'),
        'f2_desc': document.getElementById('editable-f2-desc'),
        'f3_title': document.getElementById('editable-f3-title'),
        'f3_desc': document.getElementById('editable-f3-desc'),
        'menu_subtitle': document.getElementById('editable-menu-subtitle'),
        'ann_text': document.getElementById('editable-ann-text'),
        'delivery_h2': document.getElementById('editable-delivery-h2'),
        'delivery_p': document.getElementById('editable-delivery-p'),
        'delivery_phone': document.getElementById('editable-delivery-phone'),
        'delivery_line': document.getElementById('editable-delivery-line'),
        'delivery_hours': document.getElementById('editable-delivery-hours'),
        'delivery_btn': document.getElementById('editable-delivery-btn'),
        'footer_desc': document.getElementById('footer_desc_el')
    };
    
    function syncTextToParent(key) {
        const elem = fields[key];
        if (elem && window.parent && typeof window.parent.updateLiveText === 'function') {
            window.parent.updateLiveText(key, elem.innerHTML);
        }
    }
    
    // Bind listeners
    Object.keys(fields).forEach(key => {
        const elem = fields[key];
        if (elem) {
            elem.addEventListener('input', () => syncTextToParent(key));
            elem.addEventListener('blur', () => syncTextToParent(key));
        }
    });

    // 2. Icon Picker functionality
    const pickerEmojis = ['🎂', '🍰', '🍓', '🚚', '🧁', '🍪', '🍫', '🎁', '🎉', '📍', '📞', '❤️', '🌟', '⏰', '🛍️', '🍒', '🍯', '🍩', '🎈', '🛒'];
    let activeIconKey = '';
    const picker = document.getElementById('inline-emoji-picker');
    
    if (picker) {
        // Populate emojis
        pickerEmojis.forEach(emoji => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = emoji;
            btn.style.cssText = "background: none; border: none; font-size: 1.3rem; cursor: pointer; padding: 0.2rem; border-radius: 4px; transition: 0.2s; display: flex; align-items: center; justify-content: center;";
            btn.onmouseover = () => btn.style.backgroundColor = "rgba(247, 168, 184, 0.15)";
            btn.onmouseout = () => btn.style.backgroundColor = "transparent";
            btn.onclick = (e) => {
                e.stopPropagation();
                selectEmoji(emoji);
            };
            picker.appendChild(btn);
        });

        // Close picker on outside click
        document.addEventListener('click', () => {
            picker.style.display = 'none';
        });
    }

    window.openIconPicker = function(e, key) {
        e.stopPropagation();
        activeIconKey = key;
        if (!picker) return;
        
        const rect = e.target.getBoundingClientRect();
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        picker.style.left = `${rect.left + scrollLeft}px`;
        picker.style.top = `${rect.bottom + scrollTop + 8}px`;
        picker.style.display = 'grid';
    };

    function selectEmoji(emoji) {
        if (!activeIconKey) return;
        const iconSpan = document.getElementById(`editable-${activeIconKey}-icon`);
        if (iconSpan) {
            iconSpan.innerText = emoji;
            // Sync to parent hidden inputs
            if (window.parent && typeof window.parent.updateLiveText === 'function') {
                window.parent.updateLiveText(`${activeIconKey}_icon`, emoji);
            }
        }
        if (picker) picker.style.display = 'none';
    }
});
</script>
<?php endif; ?>
</body>
</html>